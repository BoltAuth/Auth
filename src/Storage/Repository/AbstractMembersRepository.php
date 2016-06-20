<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Admin\Pager;
use Bolt\Storage\Repository;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalSingleTableAdapter;

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
     * @param QueryBuilder $query
     * @param string       $column
     *
     * @return Pager
     */
    public function getPager(QueryBuilder $query, $column)
    {
        if ($this->pager === null) {
            $adapter = new DoctrineDbalSingleTableAdapter($query, static::ALIAS . '.' . $column);
            $this->pager = new Pager($adapter);
        }

        $results = $this->pager->getCurrentPageResults();
        foreach ($results as $key => $data) {
            $entity = $this->getEntityBuilder()->getEntity();
            $this->getEntityBuilder()->createFromDatabaseValues($data, $entity);
            $results[$key] = $entity;
        }
        $this->pager->setCurrentPageResults($results);

        return $this->pager;
    }
}
