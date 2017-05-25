<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Repository;

use Bolt\Extension\BoltAuth\Auth\Storage\Entity;

/**
 * Local Oauth repository.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Oauth extends AbstractAuthRepository
{
    const ALIAS = 'o';

    /**
     * Fetches an OAuth entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Oauth|false
     */
    public function getOauthByGuid($guid)
    {
        $query = $this->getOauthByGuidQuery($guid);

        return $this->findOneWith($query);
    }

    public function getOauthByGuidQuery($guid)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('guid = :guid')
            ->setParameter('guid', $guid)
        ;

        return $qb;
    }

    /**
     * Fetches an OAuth entries by Resource Owner ID.
     *
     * @param string $resourceOwnerId
     *
     * @return Entity\Oauth|false
     */
    public function getOauthByResourceOwnerId($resourceOwnerId)
    {
        $query = $this->getOauthByResourceOwnerIdQuery($resourceOwnerId);

        return $this->findOneWith($query);
    }

    public function getOauthByResourceOwnerIdQuery($resourceOwnerId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->andWhere('resource_owner_id = :resource_owner_id')
            ->setParameter('resource_owner_id', $resourceOwnerId)
        ;

        return $qb;
    }
}
