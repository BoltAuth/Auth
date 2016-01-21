<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Storage\Database\Schema\Manager;
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
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Records
{
    /** @var Connection */
    private $connection;
    /** @var Manager */
    private $schemaManager;
    /** @var string */
    private $tableName = null;
    /** @var string */
    private $tableNameMeta = null;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->connection = $app['db'];
        $this->schemaManager = $app['schema'];
        $this->tableName = $app['schema.prefix'] . 'members';
        $this->tableNameMeta = $app['schema.prefix'] . 'members_meta';
    }

    /**
     * Get a set of members record from the database
     *
     * @return boolean|array
     */
    public function getMembers()
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableName)
            ->orderBy('id', 'ASC')
        ;
        $records = $this->connection->fetchAll($query);

        if (empty($records)) {
            return false;
        } else {
            foreach ($records as $key => $record) {
                if (isset($record['roles'])) {
                    $records[$key]['roles'] = json_decode($record['roles'], true);
                }
            }

            return $records;
        }
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
            ->from($this->tableName)
            ->where($field . ' = :value')
            ->setParameter(':value', $value)
        ;
        $record = $this->connection->fetchAssoc($query);

        if (empty($record['guid'])) {
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
            ->from($this->tableNameMeta)
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
     * @param string  $meta   Key name to search for
     * @param string  $value  Optional meta value to narrow the match
     * @param boolean $single Only return the first result
     *
     * @return array|bool
     */
    public function getMetaRecords($meta, $value = null, $single = null)
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableNameMeta)
            ->where('meta = :meta')
            ->setParameter(':meta', $meta)
        ;

        if ($value !== null) {
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
        if (!empty($userId) && $this->getMember('guid', $userId)) {
            $result = $this->connection->update($this->tableName, $values, [
                'guid' => $userId,
            ]);
        } elseif (isset($values['username']) && isset($values['displayname']) && isset($values['email'])) {
            $result = $this->connection->insert($this->tableName, $values);
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
            $result = $this->connection->update($this->tableNameMeta, $data, [
                'userid' => $userId,
                'meta'   => $meta,
            ]);
        } else {
            $result = $this->connection->insert($this->tableNameMeta, $data);
        }

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create/update database tables
     */
    public function dbCheck()
    {
        // Members table
        $this->schemaManager->registerExtensionTable(
            function (Schema $schema) {
                $table = $schema->createTable($this->tableName);

                $table->addColumn('guid',        'guid',     []);
                $table->addColumn('username',    'string',   ['length' => 32]);
                $table->addColumn('email',       'string',   ['length' => 128]);
                $table->addColumn('lastseen',    'datetime', ['default' => '1900-01-01 00:00:00']);
                $table->addColumn('lastip',      'string',   ['length' => 32, 'default' => '']);
                $table->addColumn('displayname', 'string',   ['length' => 32]);
                $table->addColumn('enabled',     'boolean',  ['default' => 0]);
                $table->addColumn('roles',       'string',   ['length' => 1024, 'default' => '']);

                $table->setPrimaryKey(['guid']);

                $table->addIndex(['username']);
                $table->addIndex(['enabled']);

                return $table;
            }
        );

        // Member meta
        $this->schemaManager->registerExtensionTable(
            function (Schema $schema) {
                $table = $schema->createTable($this->tableNameMeta);
                $table->addColumn('guid',   'guid',     []);
                $table->addColumn('userid', 'integer');
                $table->addColumn('meta',   'string',  ['length' => 64]);
                $table->addColumn('value',  'text');

                $table->setPrimaryKey(['guid']);

                $table->addIndex(['userid']);
                $table->addIndex(['meta']);

                return $table;
            }
        );
    }
}
