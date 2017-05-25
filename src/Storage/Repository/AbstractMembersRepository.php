<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Repository;

use Bolt\Extension\BoltAuth\Auth\Pager;
use Bolt\Storage\Repository;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

/**
 * Base repository for Auth.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractAuthRepository extends Repository
{
    const ALIAS = null;

    /** @var bool */
    protected $pagerEnabled;
    /** @var Pager\Pager */
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
     * @return AbstractAuthRepository
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
     * @return Pager\Pager
     */
    public function getPager(QueryBuilder $query, $column)
    {
        if ($this->pager === null) {
            $countField = static::ALIAS . '.' . $column;
            $select = $this->createSelectForCountField($countField);
            $callback = function (QueryBuilder $queryBuilder) use ($select, $countField) {
                $queryBuilder
                    ->select($select)
                    ->orderBy(1)
                    ->setMaxResults(1)
                ;
            };

            $adapter = new DoctrineDbalAdapter($query, $callback);
            $this->pager = new Pager\Pager($adapter, $this->getEntityBuilder());
        }

        return $this->pager;
    }

    private function createSelectForCountField($countField)
    {
        return sprintf('COUNT(DISTINCT %s) AS total_results', $countField);
    }
}
