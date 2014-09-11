<?php

namespace Bolt\Extension\Bolt\Membership;

use Silex;

/**
 *
 */
class MembershipRecords
{
    /**
     *
     * @var array User's profile record
     */
    public $user = false;

    /**
     * @var array User's session record
     */
    public $session = false;

    /**
     * @var Silex\Application
     */
    private $app;

    /**
     * @var Extension config array
     */
    private $config;

    public function __construct(Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['extensions.' . Extension::NAME]->config;
    }

    /**
     * Get a membership record from the database
     *
     * @param  int           $userid
     * @param  string        $meta
     * @return boolean|array
     */
    public function getRecord($userid, $meta)
    {
        $query = "SELECT * FROM " . $this->getTableName() .
                 " WHERE userid = :userid and meta = :meta";

        $map = array(
            ':userid' => $userid,
            ':meta' => $meta
        );

        $record = $this->app['db']->fetchAssoc($query, $map);

        if (empty($record['id'])) {
            return false;
        } else {
            return $record;
        }
    }

    /**
     * Update/insert a record in the database
     *
     * @param  int     $userid
     * @param  string  $meta
     * @param  string  $value
     * @return boolean
     */
    public function updateRecord($userid, $meta, $value)
    {
        $data = array(
            'userid' => $userid,
            'meta'   => $meta,
            'value'  => $value
        );

        if ($this->getRecord($userid, $meta)) {
            $result = $this->app['db']->update($this->getTableName(), $data, array(
                'userid' => $userid,
                'meta'   => $meta
            ));
        } else {
            $result = $this->app['db']->insert($this->getTableName(), $data);
        }

        if ($result) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Get the name of the user record table
     *
     * @return string
     */
    public function getTableName()
    {
        $this->prefix = $this->app['config']->get('general/database/prefix', "bolt_");

        // Make sure prefix ends in '_'. Prefixes without '_' are lame..
        if ($this->prefix[ strlen($this->prefix)-1 ] != "_") {
            $this->prefix .= "_";
        }

        return $this->prefix . 'membership';
    }

    /**
     * Create/update database tables
     */
    public function dbCheck()
    {
        // User/client provider table
        $table_name = $this->getTableName();
        $this->app['integritychecker']->registerExtensionTable(
            function ($schema) use ($table_name) {
                $table = $schema->createTable($table_name);
                $table->addColumn("id",     "integer", array('autoincrement' => true));
                $table->addColumn("userid", "integer");
                $table->addColumn("meta",   "string",  array("length" => 64));
                $table->addColumn("value",  "text");
                $table->setPrimaryKey(array("id"));
                $table->addIndex(array("userid"));
                $table->addIndex(array("meta"));

                return $table;
            }
        );
    }
}
