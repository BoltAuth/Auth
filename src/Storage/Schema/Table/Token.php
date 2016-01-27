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
        $this->table->addColumn('guid',       'guid',     []);
        $this->table->addColumn('token_type', 'string',   ['length' => 32]);
        $this->table->addColumn('token',      'string',   ['length' => 128]);
        $this->table->addColumn('token_data', 'text',     ['notnull' => false, 'default' => null]);
        $this->table->addColumn('expires',    'integer',  ['notnull' => false, 'default' => null]);
        $this->table->addColumn('cookie',     'string',   ['notnull' => false, 'default' => null, 'length' => 128]);
    }

    /**
     * @inheritDoc
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['token_type']);
        $this->table->addIndex(['token']);
        $this->table->addIndex(['expires']);
        $this->table->addIndex(['cookie']);
    }

    /**
     * @inheritDoc
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['guid']);
    }
}
