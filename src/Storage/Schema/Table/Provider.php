<?php

namespace Bolt\Extension\Bolt\Members\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Provider table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Provider extends BaseTable
{
    /**
     * @inheritDoc
     */
    protected function addColumns()
    {
        $this->table->addColumn('guid',              'guid',       []);
        $this->table->addColumn('provider',          'string',     ['length' => 64]);
        $this->table->addColumn('resource_owner_id', 'string',     ['length' => 128]);
        $this->table->addColumn('refresh_token',     'json_array', ['notnull' => false, 'default' => null, 'length' => 128]);
        $this->table->addColumn('resource_owner',    'text',       ['notnull' => false, 'default' => null]);
        $this->table->addColumn('lastupdate',        'datetime',   ['notnull' => false, 'default' => null]);
    }

    /**
     * @inheritDoc
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['provider']);
        $this->table->addIndex(['resource_owner_id']);
        $this->table->addIndex(['refresh_token']);
    }

    /**
     * @inheritDoc
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['guid']);
    }
}
