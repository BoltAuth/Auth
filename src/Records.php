<?php

namespace Bolt\Extension\Bolt\Members;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
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
    /** @var Application */
    private $app;
    /** @var array */
    private $config;
    /** @var Connection */
    private $connection;
    /** @var string */
    private $tableName = null;
    /** @var string */
    private $tableNameMeta = null;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param array       $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->connection = $app['db'];
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
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->getTableName())
            ->where($field . ' = :value')
            ->setParameter(':value', $value)
        ;
        $record = $this->connection->fetchAssoc($query);

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
     * @param int         $userId
     * @param bool|string $meta
     *
     * @return array|bool
     */
    public function getMemberMeta($userId, $meta = false)
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->getTableNameMeta())
            ->where('userid = :userid')
            ->setParameter(':userid', $userId)
        ;

        if ($meta) {
            $query->andWhere('meta = :meta')
                ->setParameter(':meta', $meta)
            ;
            $record = $this->connection->fetchAssoc($query);
        } else {
            $record = $this->connection->fetchAll($query);
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
     * @param integer $userId
     * @param string  $meta
     *
     * @return array|boolean
     */
    public function getMemberMetaValue($userId, $meta)
    {
        $record = $this->getMemberMeta($userId, $meta);

        if ($record) {
            return $record['value'];
        }

        return false;
    }

    /**
     * Get meta records from the database
     *
     * @param string      $meta   Key name to search for
     * @param bool|string $value  Optional meta value to narrow the match
     * @param boolean     $single Only return the first result
     *
     * @return array|bool
     */
    public function getMetaRecords($meta, $value = false, $single = false)
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->getTableNameMeta())
            ->where('meta = :meta')
            ->setParameter(':meta', $meta)
        ;

        if ($value) {
            $query->andWhere('value = :value')
                ->setParameter(':value', $value)
            ;
        }

        if ($single) {
            $record = $this->connection->fetchAssoc($query);
        } else {
            $record = $this->connection->fetchAll($query);
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
     * @param int   $userId
     * @param array $values
     *
     * @return boolean
     */
    public function updateMember($userId, $values)
    {
        $result = false;
        /*
         * Only do an update if there is a valid ID and the member exists
         * Only do an insert if we have a username, displayname and values to add
         */
        if (!empty($userId) && $this->getMember('id', $userId)) {
            $result = $this->connection->update($this->getTableName(), $values, [
                'id' => $userId,
            ]);
        } elseif (isset($values['username']) && isset($values['displayname']) && isset($values['email'])) {
            $result = $this->connection->insert($this->getTableName(), $values);
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
     * @param int    $userId
     * @param string $meta
     * @param string $value
     *
     * @return boolean
     */
    public function updateMemberMeta($userId, $meta, $value)
    {
        $data = [
            'userid' => $userId,
            'meta'   => $meta,
            'value'  => $value,
        ];

        if ($this->getMemberMeta($userId, $meta)) {
            $result = $this->connection->update($this->getTableNameMeta(), $data, [
                'userid' => $userId,
                'meta'   => $meta,
            ]);
        } else {
            $result = $this->connection->insert($this->getTableNameMeta(), $data);
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
        if ($this->tableName) {
            return $this->tableName;
        }
        $this->tableName = $this->app['schema.prefix'] . 'members';

        return $this->tableName;
    }

    /**
     * Get the name of the user record table
     *
     * @return string
     */
    public function getTableNameMeta()
    {
        if ($this->tableNameMeta) {
            return $this->tableNameMeta;
        }
        $this->tableNameMeta = $this->app['schema.prefix'] . 'members_meta';

        return $this->tableNameMeta;
    }

    /**
     * Create/update database tables
     */
    public function dbCheck()
    {
        // Members table
        $tableName = $this->getTableName();
        $this->app['schema']->registerExtensionTable(
            function (Schema $schema) use ($tableName) {
                $table = $schema->createTable($tableName);

                $table->addColumn('id',          'integer',  ['autoincrement' => true]);
                $table->addColumn('username',    'string',   ['length' => 32]);
                $table->addColumn('email',       'string',   ['length' => 128]);
                $table->addColumn('lastseen',    'datetime', ['default' => '1900-01-01 00:00:00']);
                $table->addColumn('lastip',      'string',   ['length' => 32, 'default' => '']);
                $table->addColumn('displayname', 'string',   ['length' => 32]);
                $table->addColumn('enabled',     'boolean',  ['default' => 0]);
                $table->addColumn('roles',       'string',   ['length' => 1024, 'default' => '']);
                $table->setPrimaryKey(['id']);
                $table->addIndex(['username']);
                $table->addIndex(['enabled']);

                return $table;
            }
        );

        // Member meta
        $tableName = $this->getTableNameMeta();
        $this->app['integritychecker']->registerExtensionTable(
            function (Schema $schema) use ($tableName) {
                $table = $schema->createTable($tableName);
                $table->addColumn('id',     'integer', ['autoincrement' => true]);
                $table->addColumn('userid', 'integer');
                $table->addColumn('meta',   'string',  ['length' => 64]);
                $table->addColumn('value',  'text');
                $table->setPrimaryKey(['id']);
                $table->addIndex(['userid']);
                $table->addIndex(['meta']);

                return $table;
            }
        );
    }
}
