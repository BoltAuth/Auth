<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Provider repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Provider extends Repository
{
    /**
     * Fetches Provider entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Provider[]
     */
    public function getProvisionsByGuid($guid)
    {
        $query = $this->getProvisionsByGuidQuery($guid);

        return $this->findWith($query);
    }

    public function getProvisionsByGuidQuery($guid)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('guid = :guid')
            ->setParameter('guid', $guid)
        ;

        return $qb;
    }

    /**
     * Fetches Provider entries by provider name.
     *
     * @param string $provider
     *
     * @return Entity\Provider[]
     */
    public function getProvisionsByProvider($provider)
    {
        $query = $this->getProvisionsByProviderQuery($provider);

        return $this->findWith($query);
    }

    public function getProvisionsByProviderQuery($provider)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('provider = :provider')
            ->setParameter('provider', $provider)
        ;

        return $qb;
    }

    /**
     * Fetches Provider entry by resource owner.
     *
     * @param string $resourceOwner
     *
     * @return Entity\Provider
     */
    public function getProvisionByResourceOwner($resourceOwner)
    {
        $query = $this->getProvisionByResourceOwnerQuery($resourceOwner);

        return $this->findWith($query);
    }

    public function getProvisionByResourceOwnerQuery($resourceOwner)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('resource_owner = :resource_owner')
            ->setParameter('resource_owner', $resourceOwner)
        ;

        return $qb;
    }

    /**
     * Fetches an Provider entries by resource owner ID.
     *
     * @param string $resourceOwnerId
     *
     * @return Entity\Provider
     */
    public function getProvisionByResourceOwnerId($resourceOwnerId)
    {
        $query = $this->getProvisionByResourceOwnerIdQuery($resourceOwnerId);

        return $this->findWith($query);
    }

    public function getProvisionByResourceOwnerIdQuery($resourceOwnerId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('resource_owner_id = :resource_owner_id')
            ->setParameter('resource_owner_id', $resourceOwnerId)
        ;

        return $qb;
    }

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
