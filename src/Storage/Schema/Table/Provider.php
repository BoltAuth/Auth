<?php

namespace Bolt\Extension\Bolt\Members\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Provider table.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
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
        $this->table->addColumn('refresh_token',     'json_array', ['default' => []]);
        $this->table->addColumn('resource_owner',    'json_array', ['default' => []]);
        $this->table->addColumn('lastupdate',        'datetime',   ['notnull' => false, 'default' => null]);
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

        // Temporary until done upstream
        $this->addForeignKeyConstraint();
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
    protected function addForeignKeyConstraint()
    {
        $this->table->addForeignKeyConstraint('bolt_members_account', ['guid'], ['guid'], [], 'guid_provider');
    }
}
