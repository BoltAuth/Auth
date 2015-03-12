<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;
use Silex\Application;

/**
 * Members record class
 *
 * NOTE:
 * Do NOT call this class from external extensions.
 * Use Bolt\Extension\Bolt\Members\Members instead
 *
 * Copyright (C) 2014  Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class Records
{
    /**
     * @var Silex\Application
     */
    private $app;

    /**
     * @var Extension config array
     */
    private $config;

    /**
     * @var string
     */
    private $tablename = null;

    /**
     * @var string
     */
    private $tablename_meta = null;

    /**
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
    }

    /**
     * Get a members record from the database
     *
     * @param string $field The field to query on (id, username or email)
     * @param string $value The value to match
     *
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
            if (isset($record['roles'])) {
                $record['roles'] = json_decode($record['roles'], true);
            }

            return $record;
        }
    }

    /**
     * Get a members meta record from the database
     *
     * @param int    $userid
     * @param string $meta
     *
     * @return boolean|array
     */
    public function getMemberMeta($userid, $meta = false)
    {
        if ($meta) {
            $query = "SELECT * FROM " . $this->getTableNameMeta() .
                     " WHERE userid = :userid and meta = :meta";

            $map = array(
                ':userid' => $userid,
                ':meta'   => $meta
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
     *
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
     * @param string  $meta   Key name to search for
     * @param string  $value  Optional meta value to narrow the match
     * @param boolean $single Only return the first result
     *
     * @return boolean|array
     */
    public function getMetaRecords($meta, $value = false, $single = false)
    {
        if ($value) {
            $query = "SELECT * FROM " . $this->getTableNameMeta() .
                     " WHERE meta = :meta AND value = :value";

            $map = array(
                ':meta'  => $meta,
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
     * @param int   $userid
     * @param array $values
     *
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
        } elseif (isset($values['username']) && isset($values['displayname']) && isset($values['email'])) {
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
     * @param int    $userid
     * @param string $meta
     * @param string $value
     *
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
        if ($this->tablename) {
            return $this->tablename;
        }

        $prefix = $this->app['config']->get('general/database/prefix', "bolt_");
        if ($prefix[ strlen($prefix)-1 ] != "_") {
            $prefix .= "_";
        }

        $this->tablename = $prefix . 'members';

        return $this->tablename;
    }

    /**
     * Get the name of the user record table
     *
     * @return string
     */
    public function getTableNameMeta()
    {
        if ($this->tablename_meta) {
            return $this->tablename_meta;
        }

        $prefix = $this->app['config']->get('general/database/prefix', "bolt_");
        if ($prefix[ strlen($prefix)-1 ] != "_") {
            $prefix .= "_";
        }

        $this->tablename_meta = $prefix . 'members_meta';

        return $this->tablename_meta;
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
                $table->addColumn('lastseen',    'datetime', array('default' => '1900-01-01 00:00:00'));
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
