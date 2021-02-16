<?php

namespace Bolt\Extension\BoltAuth\Auth\Pager;

use Bolt\Storage\Entity\Builder;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * Pager for auth data.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Pager extends Pagerfanta
{
    /** @var Builder */
    private $builder;
    /** @var bool */
    private $built;

    /**
     * {@inheritdoc}
     */
    public function __construct(AdapterInterface $adapter, Builder $builder)
    {
        parent::__construct($adapter);

        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPageResults()
    {
        $results = parent::getCurrentPageResults();
        if ($this->built === null) {
            foreach ($results as $key => $data) {
                $entity = $this->builder->getEntity();
                $this->builder->createFromDatabaseValues($data, $entity);
                $results[$key] = $entity;
            }
            $this->setCurrentPageResults($results);
            $this->built = true;
        }

        return $results;
    }

    /**
     * @param array|\Traversable $currentPageResults
     */
    private function setCurrentPageResults($currentPageResults)
    {
        $reflection = new \ReflectionClass($this);
        $prop = $reflection->getParentClass()->getProperty('currentPageResults');
        $prop->setAccessible(true);
        $prop->setValue($this, $currentPageResults);
    }
}
