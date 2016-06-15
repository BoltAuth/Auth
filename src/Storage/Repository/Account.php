<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Events\StorageEvent;
use Bolt\Events\StorageEvents;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Storage\QuerySet;
use Bolt\Storage\Repository;
use Pagerfanta\Pagerfanta as Pager;
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
class Account extends AbstractMembersRepository
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
        /** @var \Bolt\Extension\Bolt\Members\Storage\Entity\Account $entity */
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
     * @return Entity\Account[]|Pager
     */
    public function getAccounts()
    {
        $query = $this->getAccountsQuery();

        if ($this->pagerEnabled) {
            return $this->getPager($query, 'guid');
        }

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
     * Fetches an account by meta key/value.
     *
     * @param string $guid
     * @param string $meta
     * @param string $value
     *
     * @return Entity\Account
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
     * @return Entity\Account[]|Pager
     */
    public function getAccountsByEnableStatus($status)
    {
        $query = $this->getAccountsByEnableStatusQuery($status);

        if ($this->pagerEnabled) {
            return $this->getPager($query, 'guid');
        }

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
}
