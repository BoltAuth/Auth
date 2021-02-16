<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Repository;

use Bolt\Extension\BoltAuth\Auth\Storage\Entity;

/**
 * Provider repository.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Provider extends AbstractAuthRepository
{
    const ALIAS = 'p';

    /**
     * Fetches Provider entries by GUID & provider name.
     *
     * @param string $guid
     * @param string $provider
     *
     * @return Entity\Provider|false
     */
    public function getProvision($guid, $provider)
    {
        $query = $this->getProvisionQuery($guid, $provider);

        return $this->findOneWith($query);
    }

    public function getProvisionQuery($guid, $provider)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('guid = :guid')
            ->andWhere('provider = :provider')
            ->setParameter('guid', $guid)
            ->setParameter('provider', $provider)
        ;

        return $qb;
    }

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
     * @return Entity\Provider[]
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
     * @param string $provider
     * @param string $resourceOwnerId
     *
     * @return Entity\Provider|false
     */
    public function getProvisionByResourceOwnerId($provider, $resourceOwnerId)
    {
        $query = $this->getProvisionByResourceOwnerIdQuery($provider, $resourceOwnerId);

        return $this->findOneWith($query);
    }

    public function getProvisionByResourceOwnerIdQuery($provider, $resourceOwnerId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('provider = :provider')
            ->where('resource_owner_id = :resource_owner_id')
            ->setParameter('provider', $provider)
            ->setParameter('resource_owner_id', $resourceOwnerId)
        ;

        return $qb;
    }
}
