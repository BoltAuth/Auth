<?php

namespace Bolt\Extension\Bolt\Members\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Token table.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Token extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',         'integer',    ['autoincrement' => true]);
        $this->table->addColumn('token',      'string',     ['length' => 128]);
        $this->table->addColumn('token_type', 'string',     ['length' => 32]);
        $this->table->addColumn('token_data', 'json_array', []);
        $this->table->addColumn('expires',    'integer',    ['notnull' => false, 'default' => null]);
        $this->table->addColumn('guid',       'guid',       []);
        $this->table->addColumn('cookie',     'string',     ['notnull' => false, 'default' => null, 'length' => 128]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['token_type']);
        $this->table->addIndex(['expires']);
        $this->table->addIndex(['guid']);
        $this->table->addIndex(['cookie']);

        $this->table->addUniqueIndex(['guid', 'cookie']);
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
    }
}
