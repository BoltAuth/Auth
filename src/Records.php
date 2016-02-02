<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\Bolt\Members\Entity\Profile;
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
    private $tableName;
    /** @var string */
    private $tableNameMeta;

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
     * @return Profile[]|null
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
            return null;
        } else {
            foreach ($records as $key => $record) {
                if (isset($record['roles'])) {
                    $records[$key] = new Profile($record, $this);
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
     * @return Profile|null
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
            return null;
        } else {
            if (isset($record['roles'])) {
                $record['roles'] = json_decode($record['roles'], true);
            }

            return new Profile($record, $this);
        }
    }

    /**
     * Look up a member based on their OAuth provider GUID.
     *
     * @param $guid
     *
     * @return Profile
     */
    public function getMemberByProviderId($guid)
    {
        $guid = $this->getSafeGuid($guid);
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableNameMeta, 'm')
            ->where("m.value = '$guid'")
            //->where('m.value = :value')
            //->setParameter(':value', $guid, \Doctrine\DBAL\Types\Type::GUID)
        ;
        $record = $this->connection->fetchAssoc($query);

        if ($record === false) {
            return;
        }

        return $this->getMember('guid', $record['guid']);
    }

    /**
     * Get a members meta record from the database
     *
     * @param string $guid
     * @param string $meta
     *
     * @return array|null
     */
    public function getMemberMeta($guid, $meta = null)
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableNameMeta)
            ->where("guid = '$guid'")
            //->where('guid = :guid')
            //->setParameter(':guid', $guid)
        ;

        if ($meta) {
            $query->andWhere('meta = :meta')
                ->setParameter(':meta', $meta)
            ;
            $record = $this->connection->fetchAssoc($query);
        } else {
            $record = $this->connection->fetchAll($query);
        }

        return empty($record) ? null : $record;
    }

    /**
     * Return the value of a single meta record for a user
     *
     * @param integer $guid
     * @param string  $meta
     *
     * @return array|null
     */
    public function getMemberMetaValue($guid, $meta)
    {
        $record = $this->getMemberMeta($guid, $meta);

        return $record ? $record['value'] : null;
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
     * @param int   $guid
     * @param array $values
     *
     * @return boolean
     */
    public function updateMember($guid, $values)
    {
        $result = false;
        /*
         * Only do an update if there is a valid ID and the member exists
         * Only do an insert if we have a username, displayname and values to add
         */
        if (!empty($guid) && $this->getMember('guid', $guid)) {
            $result = $this->connection->update($this->tableName, $values, [
                'guid' => $guid,
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
     * @param int    $guid
     * @param string $meta
     * @param string $value
     *
     * @return boolean
     */
    public function updateMemberMeta($guid, $meta, $value)
    {
        $data = [
            'userid' => $guid,
            'meta'   => $meta,
            'value'  => $value,
        ];

        if ($this->getMemberMeta($guid, $meta)) {
            $result = $this->connection->update($this->tableNameMeta, $data, [
                'userid' => $guid,
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
                $table->addColumn('meta',   'string',  ['length' => 64]);
                $table->addColumn('value',  'text');

                $table->setPrimaryKey(['guid']);

                $table->addIndex(['meta']);

                $table->addForeignKeyConstraint($this->tableName, ['guid'], ['guid']);

                return $table;
            }
        );
    }

    /**
     * Temporary function for minimal parsing of GUIDs until we can figure out why DBAL is boking on them as parameters.
     *
     * @param string $guid
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private function getSafeGuid($guid)
    {
        if (!is_string($guid)) {
            throw new \InvalidArgumentException('Invalid GUID string.');
        }

        $parts = explode('-', $guid);
        if (count($parts) !== 5) {
            throw new \InvalidArgumentException('Invalid GUID string.');
        }

        if (strlen($parts[0]) === 8 && strlen($parts[1]) === 4 && strlen($parts[2]) === 4 && strlen($parts[3]) === 4 && strlen($parts[4]) === 12) {
            return $guid;
        }

        throw new \InvalidArgumentException('Invalid GUID string.');
    }
}
