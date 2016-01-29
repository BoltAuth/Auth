<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Storage\Entity\Entity;
use Bolt\Storage\QuerySet;
use Bolt\Storage\Repository;

/**
 * Bolt core ID/GUID mapping.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractGuidRepository extends Repository
{
    /**
     * {@inheritdoc}
     */
    public function insert(Entity $entity)
    {
        $querySet = new QuerySet();
        $qb = $this->em->createQueryBuilder();
        $qb->insert($this->getTableName());
        $querySet->append($qb);
        $this->persist($querySet, $entity, ['id']);

        $result = $querySet->execute();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function update($entity, $exclusions = [])
    {
        $querySet = new QuerySet();
        $querySet->setParentId($entity->getGuid());
        $qb = $this->em->createQueryBuilder();
        $qb->update($this->getTableName())
            ->where('guid = :guid')
            ->setParameter('guid', $entity->getGuid());
        $querySet->append($qb);
        $this->persist($querySet, $entity, ['id'] + $exclusions);

        return $querySet->execute();
    }

    /**
     * Creates a query builder instance namespaced to this repository.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->from($this->getTableName());
    }
}
