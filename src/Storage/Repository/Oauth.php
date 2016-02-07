<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Local Oauth repository.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Oauth extends AbstractMembersRepository
{
    /**
     * Fetches an OAuth entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Oauth
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
     * @return Entity\Oauth
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
