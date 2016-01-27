<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Account repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Account extends Repository
{
    /**
     * Fetches an account by GUID.
     *
     * @return Entity\Account[]
     */
    public function getAccounts()
    {
        $query = $this->getAccountsQuery();

        return $this->findWith($query);
    }

    public function getAccountsQuery()
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*');

        return $qb;
    }

    /**
     * Fetches an account by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Account
     */
    public function getAccountByGuid($guid)
    {
        $query = $this->getAccountByGuidQuery($guid);

        return $this->findOneWith($query);
    }

    public function getAccountByGuidQuery($guid)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('guid = :guid')
            ->setParameter('guid', $guid)
        ;

        return $qb;
    }

    /**
     * Fetches an account by email.
     *
     * @param string $email
     *
     * @return Entity\Account
     */
    public function getAccountByEmail($email)
    {
        $query = $this->getAccountByEmailQuery($email);

        return $this->findOneWith($query);
    }

    public function getAccountByEmailQuery($email)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('email = :email')
            ->setParameter('email', $email)
        ;

        return $qb;
    }

    /**
     * Fetches an account by account name.
     *
     * @param string $username
     *
     * @return Entity\Account
     */
    public function getAccountByUserName($username)
    {
        $query = $this->getAccountByUserNameQuery($username);

        return $this->findOneWith($query);
    }

    public function getAccountByUserNameQuery($username)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('username = :username')
            ->setParameter('username', $username)
        ;

        return $qb;
    }

    /**
     * Returns all accounts that are either enabled, or disabled.
     *
     * @param boolean $status
     *
     * @return Entity\Account[]
     */
    public function getAccountsByEnableStatus($status)
    {
        $query = $this->getAccountsByEnableStatusQuery($status);

        return $this->findOneWith($query);
    }

    public function getAccountsByEnableStatusQuery($status)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('status = :status')
            ->setParameter('status', $status)
        ;

        return $qb;
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
