<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Repository;

use Bolt\Events\StorageEvent;
use Bolt\Events\StorageEvents;
use Bolt\Extension\BoltAuth\Auth\Pager\Pager;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity;
use Bolt\Storage\QuerySet;
use Ramsey\Uuid\Uuid;

/**
 * Account repository.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Account extends AbstractAuthRepository
{
    const ALIAS = 'a';

    /**
     * {@inheritdoc}
     */
    public function save($entity, $silent = null)
    {
        try {
            $existing = $entity->getGuid();
        } catch (\Exception $e) {
            $existing = false;
        }

        if ($existing) {
            $response = $this->update($entity);
        } else {
            $response = $this->insert($entity);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($entity)
    {
        /** @var \Bolt\Extension\BoltAuth\Auth\Storage\Entity\Account $entity */
        $entity->setGuid(Uuid::uuid4()->toString());
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
        /** @var \Bolt\Storage\Entity\Entity $entity */
        $this->persist($querySet, $entity, ['id'] + $exclusions);

        return $querySet->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity)
    {
        $event = new StorageEvent($entity);
        $this->event()->dispatch(StorageEvents::PRE_DELETE, $event);
        $qb = $this->em->createQueryBuilder()
            ->delete($this->getTableName())
            ->where('guid = :guid')
            ->setParameter('guid', $entity->getGuid());

        $response = $qb->execute();
        $event = new StorageEvent($entity);
        $this->event()->dispatch(StorageEvents::POST_DELETE, $event);

        return $response;
    }

    /**
     * Fetches all accounts.
     *
     * @param string $orderBy
     * @param string $order
     *
     * @return Entity\Account[]|Pager
     */
    public function getAccounts($orderBy = 'displayname', $order = null)
    {
        $query = $this->getAccountsQuery($orderBy, $order);

        if ($this->pagerEnabled) {
            return $this->getPager($query, 'guid');
        }

        return $this->findWith($query);
    }

    public function getAccountsQuery($orderBy, $order)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->orderBy($orderBy, $order)
        ;

        return $qb;
    }

    /**
     * Fetches an account by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Account|false
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
     * @return Entity\Account|false
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
            ->where('lower(email) = :email')
            ->setParameter('email', strtolower($email))
        ;

        return $qb;
    }

    /**
     * Fetches an account by account name.
     *
     * @param string $username
     *
     * @return Entity\Account|false
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
     * Fetches an account by meta key/value.
     *
     * @param string $guid
     * @param string $meta
     * @param string $value
     *
     * @return Entity\Account|false
     */
    public function getAccountByMeta($guid, $meta, $value)
    {
        $query = $this->getAccountByMetaQuery($guid, $meta, $value);

        return $this->findOneWith($query);
    }

    public function getAccountByMetaQuery($guid, $meta, $value)
    {
        $metaTableName = $this->getEntityManager()
            ->getRepository(Entity\AccountMeta::class)
            ->getTableName()
        ;

        $qb = $this->createQueryBuilder();
        $qb->select(static::ALIAS . '.*')
            ->leftJoin('a', $metaTableName, 'm', 'a.guid = m.guid')
            ->where('a.guid = :guid')
            ->andWhere('m.meta = :meta')
            ->andWhere('m.value = :value')
            ->setParameter('guid', $guid)
            ->setParameter('meta', $meta)
            ->setParameter('value', $value)
        ;

        return $qb;
    }

    /**
     * Returns all accounts that are either enabled, or disabled.
     *
     * @param boolean $status
     *
     * @return Entity\Account[]|Pager|false
     */
    public function getAccountsByEnableStatus($status)
    {
        $query = $this->getAccountsByEnableStatusQuery($status);

        if ($this->pagerEnabled) {
            return $this->getPager($query, 'guid');
        }

        return $this->findWith($query);
    }

    public function getAccountsByEnableStatusQuery($status)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('enabled = :status')
            ->setParameter('status', $status, \PDO::PARAM_INT)
        ;

        return $qb;
    }

    /**
     * Search for auth accounts.
     *
     * @param string $term
     * @param string $orderBy
     * @param null   $order
     *
     * @return array|\Bolt\Extension\BoltAuth\Auth\Pager\Pager
     */
    public function search($term, $orderBy = 'displayname', $order = null)
    {
        $query = $this->searchQuery($term, $orderBy, $order);

        if ($this->pagerEnabled) {
            return $this->getPager($query, 'guid');
        }

        return $this->findWith($query);
    }

    public function searchQuery($term, $orderBy, $order)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where($qb->expr()->like('displayname', ':term'))
            ->orWhere($qb->expr()->like('email', ':term'))
            ->setParameter('term', '%' . $term . '%')
            ->orderBy($orderBy, $order)
        ;

        return $qb;
    }
}
