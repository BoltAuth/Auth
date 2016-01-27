<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Storage\Repository;

/**
 * Oauth repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Oauth extends Repository
{
    /**
     * Creates a query builder instance namespaced to this repository
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createQueryBuilder($alias = null)
    {
        return $this->em->createQueryBuilder()
            ->from($this->getTableName());
    }
}
