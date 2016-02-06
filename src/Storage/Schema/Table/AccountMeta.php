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
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',     'integer', ['autoincrement' => true]);
        $this->table->addColumn('guid',   'guid',    []);
        $this->table->addColumn('meta',   'string',  ['length' => 64]);
        $this->table->addColumn('value',  'text',    ['notnull' => false, 'default' => null]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['guid']);
        $this->table->addIndex(['meta']);

        $this->table->addUniqueIndex(['guid', 'meta']);

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
        $this->table->addForeignKeyConstraint('bolt_members_account', ['guid'], ['guid'], [], 'guid_account_meta');
    }
}
