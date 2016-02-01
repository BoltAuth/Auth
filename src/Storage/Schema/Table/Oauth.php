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
        $this->table->addColumn('id',                'integer', ['autoincrement' => true]);
        $this->table->addColumn('guid',              'guid',    []);
        $this->table->addColumn('resource_owner_id', 'string',  ['notnull' => false, 'length' => 128]);
        $this->table->addColumn('password',          'string',  ['notnull' => false, 'length' => 64]);
        $this->table->addColumn('email',             'string',  ['notnull' => false, 'length' => 254]);
        $this->table->addColumn('enabled',           'boolean', ['default' => false]);
    }

    /**
     * @inheritDoc
     */
    protected function addIndexes()
    {
        $this->table->addUniqueIndex(['resource_owner_id']);
        $this->table->addUniqueIndex(['email']);
        $this->table->addIndex(['guid']);
        $this->table->addIndex(['enabled']);

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
        $this->table->addForeignKeyConstraint('bolt_members_account', ['guid'], ['guid'], [], 'guid');
    }
}
