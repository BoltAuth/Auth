<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Oauth repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Oauth extends AbstractMembersRepository
{
    /**
     * Fetches an OAuth entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Oauth[]
     */
    public function getOauthByGuid($guid)
    {
        $query = $this->getOauthByGuidQuery($guid);

        return $this->findWith($query);
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
     * Fetches an OAuth entries by GUID.
     *
     * @param string $email
     *
     * @return Entity\Oauth
     */
    public function getOauthByEmail($email)
    {
        $query = $this->getOauthByEmailQuery($email);

        return $this->findOneWith($query);
    }

    public function getOauthByEmailQuery($email)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('email = :email')
            ->setParameter('email', $email)
        ;

        return $qb;
    }

    /**
     * Fetches an OAuth entries by Resource Owner ID.
     *
     * @param string $guid
     * @param string $resourceOwnerId
     *
     * @return Entity\Oauth
     */
    public function getOauthByResourceOwnerId($guid, $resourceOwnerId)
    {
        $query = $this->getOauthByResourceOwnerIdQuery($guid, $resourceOwnerId);

        return $this->findOneWith($query);
    }

    public function getOauthByResourceOwnerIdQuery($guid, $resourceOwnerId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('guid = :guid')
            ->andWhere('resource_owner_id = :resource_owner_id')
            ->setParameter('guid', $guid)
            ->setParameter('resource_owner_id', $resourceOwnerId)
        ;

        return $qb;
    }
}
