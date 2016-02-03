<?php

namespace Bolt\Extension\Bolt\Members\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Token table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Token extends BaseTable
{
    /**
     * @inheritDoc
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',         'integer',    ['autoincrement' => true]);
        $this->table->addColumn('token',      'string',     ['length' => 128]);
        $this->table->addColumn('token_type', 'string',     ['length' => 32]);
        $this->table->addColumn('token_data', 'json_array', ['notnull' => false, 'default' => null]);
        $this->table->addColumn('expires',    'integer',    ['notnull' => false, 'default' => null]);
        $this->table->addColumn('guid',       'guid',       []);
        $this->table->addColumn('cookie',     'string',     ['notnull' => false, 'default' => null, 'length' => 128]);
    }

    /**
     * @inheritDoc
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['token_type']);
        $this->table->addIndex(['expires']);
        $this->table->addIndex(['guid']);
        $this->table->addIndex(['cookie']);

        $this->table->addUniqueIndex(['guid', 'cookie']);

        // Temporary until done upstream
        $this->addForeignKeyConstraint();
    }

    /**
     * @inheritDoc
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['id']);
    }

    /**
     * @inheritDoc
     */
    protected function addForeignKeyConstraint()
    {
        $this->table->addForeignKeyConstraint('bolt_members_account', ['guid'], ['guid'], [], 'guid_constraint');
    }
}
