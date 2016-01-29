<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Oauth repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Oauth extends AbstractGuidRepository
{
    /**
     * Fetches an OAuth entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Account
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
     * Fetches an OAuth entries by GUID.
     *
     * @param string $email
     *
     * @return Entity\Account
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
     * @param string $resourceOwnerId
     *
     * @return Entity\Account
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
    public function createQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->from($this->getTableName());
    }
}
