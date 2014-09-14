<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;

/**
 *
 */
class MembersRecords
{
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
     * Get a members record from the database
     *
     * @param  string        $field The field to query on (id, username or email)
     * @param  string        $value The value to match
     * @return boolean|array
     */
    public function getMember($field, $value)
    {
        $query = "SELECT * FROM " . $this->getTableName() . " WHERE {$field} = :value";

        $map = array(
            ':field' => $field,
            ':value' => $value
        );

        $record = $this->app['db']->fetchAssoc($query, $map);

        if (empty($record['id'])) {
            return false;
        } else {
            return $record;
        }
    }

    /**
     * Get a members meta record from the database
     *
     * @param  int           $userid
     * @param  string        $meta
     * @return boolean|array
     */
    public function getMemberMeta($userid, $meta = false)
    {
        if ($meta) {
            $query = "SELECT * FROM " . $this->getTableNameMeta() .
                     " WHERE userid = :userid and meta = :meta";

            $map = array(
                ':userid' => $userid,
                ':meta' => $meta
            );

            $record = $this->app['db']->fetchAssoc($query, $map);
        } else {
            $query = "SELECT * FROM " . $this->getTableNameMeta() .
                     " WHERE userid = :userid";

            $map = array(
                ':userid' => $userid
            );

            $record = $this->app['db']->fetchAll($query, $map);
        }

        if (empty($record)) {
            return false;
        } else {
            return $record;
        }
    }

    /**
     * Return the value of a single meta record for a user
     *
     * @param integer $userid
     * @param string  $meta
     * @return array|boolean
     */
    public function getMemberMetaValue($userid, $meta)
    {
        $record = $this->getMemberMeta($userid, $meta);

        if ($record) {
            return $record['value'];
        }

        return false;
    }

    /**
     * Get meta records from the database
     *
     * @param  string        $meta   Key name to search for
     * @param  string        $value  Optional meta value to narrow the match
     * @param  boolean       $single Only return the first result
     * @return boolean|array
     */
    public function getMetaRecords($meta, $value = false, $single = false)
    {
        if ($value) {
            $query = "SELECT * FROM " . $this->getTableNameMeta() .
                     " WHERE meta = :meta AND value = :value";

            $map = array(
                ':meta' => $meta,
                ':value' => $value
            );
        } else {
            $query = "SELECT * FROM " . $this->getTableNameMeta() .
                     " WHERE meta = :meta";

            $map = array(
                ':meta' => $meta
            );
        }

        if ($single) {
            $record = $this->app['db']->fetchAssoc($query, $map);
        } else {
            $record = $this->app['db']->fetchAll($query, $map);
        }

        if (empty($record)) {
            return false;
        } else {
            return $record;
        }
    }

    /**
     * Update/insert a member record in the database
     *
     * @param  int     $userid
     * @param  string  $meta
     * @param  string  $value
     * @return boolean
     */
    public function updateMember($userid, $values)
    {
        /*
         * Only do an update if there is a valid ID and the member exists
         * Only do an insert if we have a username, displayname and values to add
         */
        if (! empty($userid) && $this->getMember('id', $userid)) {
            $result = $this->app['db']->update($this->getTableName(), $values, array(
                'id' => $userid
            ));
        } elseif (isset($values['username']) && isset($values['displayname']) && isset($values['values'])) {
            $result = $this->app['db']->insert($this->getTableName(), $values);
        }

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update/insert a member's meta record in the database
     *
     * @param  int     $userid
     * @param  string  $meta
     * @param  string  $value
     * @return boolean
     */
    public function updateMemberMeta($userid, $meta, $value)
    {
        $data = array(
            'userid' => $userid,
            'meta'   => $meta,
            'value'  => $value
        );

        if ($this->getMemberMeta($userid, $meta)) {
            $result = $this->app['db']->update($this->getTableNameMeta(), $data, array(
                'userid' => $userid,
                'meta'   => $meta
            ));
        } else {
            $result = $this->app['db']->insert($this->getTableNameMeta(), $data);
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

        return $this->prefix . 'members';
    }

    /**
     * Get the name of the user record table
     *
     * @return string
     */
    public function getTableNameMeta()
    {
        $this->prefix = $this->app['config']->get('general/database/prefix', "bolt_");

        // Make sure prefix ends in '_'. Prefixes without '_' are lame..
        if ($this->prefix[ strlen($this->prefix)-1 ] != "_") {
            $this->prefix .= "_";
        }

        return $this->prefix . 'members_meta';
    }

    /**
     * Create/update database tables
     */
    public function dbCheck()
    {
        // Members table
        $table_name = $this->getTableName();
        $this->app['integritychecker']->registerExtensionTable(
            function ($schema) use ($table_name) {
                $table = $schema->createTable($table_name);

                $table->addColumn('id',          'integer',  array('autoincrement' => true));
                $table->addColumn('username',    'string',   array('length' => 32));
                $table->addColumn('email',       'string',   array('length' => 128));
                $table->addColumn('lastseen',    'datetime', array('default' => '0000-00-00 00:00:00'));
                $table->addColumn('lastip',      'string',   array('length' => 32, 'default' => ''));
                $table->addColumn('displayname', 'string',   array('length' => 32));
                $table->addColumn('enabled',     'boolean',  array('default' => 0));
                $table->addColumn('roles',       'string',   array('length' => 1024, 'default' => ''));
                $table->setPrimaryKey(array('id'));
                $table->addIndex(array('username'));
                $table->addIndex(array('enabled'));

                return $table;
            }
        );

        // Member meta
        $table_name = $this->getTableNameMeta();
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
