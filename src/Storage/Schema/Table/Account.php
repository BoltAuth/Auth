<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Account table.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Account extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('guid',        'guid',       []);
        $this->table->addColumn('email',       'string',     ['length'  => 254]);
        $this->table->addColumn('displayname', 'string',     ['length'  => 128, 'notnull' => false]);
        $this->table->addColumn('enabled',     'boolean',    ['default' => 0]);
        $this->table->addColumn('verified',    'boolean',    ['default' => 0]);
        $this->table->addColumn('roles',       'json_array', []);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addUniqueIndex(['email']);

        $this->table->addIndex(['enabled']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['guid']);
    }
}
