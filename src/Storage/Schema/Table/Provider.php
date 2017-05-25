<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Provider table.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Provider extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',                'integer',    ['autoincrement' => true]);
        $this->table->addColumn('guid',              'guid',       []);
        $this->table->addColumn('provider',          'string',     ['length' => 64]);
        $this->table->addColumn('resource_owner_id', 'string',     ['notnull' => false, 'default' => null, 'length' => 128]);
        $this->table->addColumn('refresh_token',     'json_array', ['notnull' => false]);
        $this->table->addColumn('resource_owner',    'json_array', ['notnull' => false]);
        $this->table->addColumn('lastupdate',        'datetime',   ['notnull' => false, 'default' => null]);
        $this->table->addColumn('lastseen',          'datetime',   ['notnull' => false, 'default' => null]);
        $this->table->addColumn('lastip',            'string',     ['length'  => 45, 'notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['guid']);
        $this->table->addIndex(['provider']);
        $this->table->addIndex(['resource_owner_id']);

        $this->table->addUniqueIndex(['provider', 'resource_owner_id']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['id']);
    }

    /**
     * {@inheritdoc}
     */
    protected function addForeignKeyConstraints()
    {
        $this->table->addForeignKeyConstraint('bolt_auth_account', ['guid'], ['guid'], ['onDelete' => 'CASCADE']);
    }
}
