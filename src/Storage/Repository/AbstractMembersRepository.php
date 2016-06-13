<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Storage\Repository;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalSingleTableAdapter;
use Pagerfanta\Pagerfanta as Pager;

/**
 * Base repository for Members.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractMembersRepository extends Repository
{
    const ALIAS = null;

    /** @var bool */
    protected $pagerEnabled;
    /** @var Pager */
    protected $pager;

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias = null)
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->from($this->getTableName(), static::ALIAS)
        ;

        return $queryBuilder;
    }

    /**
     * @return boolean
     */
    public function isPagerEnabled()
    {
        return $this->pagerEnabled;
    }

    /**
     * @param boolean $pagerEnabled
     *
     * @return AbstractMembersRepository
     */
    public function setPagerEnabled($pagerEnabled)
    {
        $this->pagerEnabled = $pagerEnabled;

        return $this;
    }

    /**
     * @return Pager
     */
    public function getPager(QueryBuilder $query, $column)
    {
        $adapter = new DoctrineDbalSingleTableAdapter($query, static::ALIAS . '.' . $column);
        $this->pager = new Pager($adapter);

        return $this->pager;
    }
}
