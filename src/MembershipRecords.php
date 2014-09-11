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

                return $table;
            }
        );
    }
}
