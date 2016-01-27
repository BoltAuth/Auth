<?php

namespace Bolt\Extension\Bolt\Members\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Account meta table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccountMeta extends BaseTable
{
    /**
     * @inheritDoc
     */
    protected function addColumns()
    {
        $this->table->addColumn('guid',   'guid',     []);
        $this->table->addColumn('meta',   'string',  ['length' => 64]);
        $this->table->addColumn('value',  'text');
    }

    /**
     * @inheritDoc
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['meta']);

        //$this->table->addForeignKeyConstraint($this->tableName, ['guid'], ['guid']);
    }

    /**
     * @inheritDoc
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['guid']);
    }
}
