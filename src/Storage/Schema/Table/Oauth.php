<?php

namespace Bolt\Extension\Bolt\Members\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Oauth table.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Oauth extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',                'integer', ['autoincrement' => true]);
        $this->table->addColumn('guid',              'guid',    []);
        $this->table->addColumn('resource_owner_id', 'string',  ['notnull' => false, 'length' => 128]);
        $this->table->addColumn('password',          'string',  ['notnull' => false, 'length' => 64]);
        $this->table->addColumn('enabled',           'boolean', ['default' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addUniqueIndex(['resource_owner_id']);
        $this->table->addIndex(['guid']);
        $this->table->addIndex(['enabled']);
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
        $this->table->addForeignKeyConstraint('bolt_members_account', ['guid'], ['guid'], ['onDelete' => 'CASCADE']);
        // This breaks on MySQL
        //$this->table->addForeignKeyConstraint('bolt_members_provider', ['guid', 'resource_owner_id'], ['guid', 'resource_owner_id'], ['onDelete' => 'CASCADE']);
    }
}
