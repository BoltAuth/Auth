<?php

namespace Bolt\Extension\Bolt\Members\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Oauth table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Oauth extends BaseTable
{
    /**
     * @inheritDoc
     */
    protected function addColumns()
    {
        $this->table->addColumn('guid',              'guid',     []);
        $this->table->addColumn('resource_owner_id', 'string',   ['notnull' => false, 'length' => 128]);
        $this->table->addColumn('password',          'string',   ['notnull' => false, 'length' => 64]);
        $this->table->addColumn('email',             'string',   ['notnull' => false, 'length' => 254]);
        $this->table->addColumn('enabled',           'boolean',  ['default' => false]);
    }

    /**
     * @inheritDoc
     */
    protected function addIndexes()
    {
        $this->table->addUniqueIndex(['resource_owner_id']);
        $this->table->addUniqueIndex(['email']);
        $this->table->addIndex(['enabled']);
    }

    /**
     * @inheritDoc
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['guid']);
    }

}
