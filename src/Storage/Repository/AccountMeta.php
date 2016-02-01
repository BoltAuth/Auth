<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Account meta repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccountMeta extends AbstractMembersRepository
{
    /**
     * Fetches all meta data for an account.
     *
     * @param string $guid
     *
     * @return Entity\AccountMeta[]
     */
    public function getAccountMetaAll($guid)
    {
        $query = $this->getAccountMetaAllQuery($guid);

        return $this->findWith($query);
    }

    public function getAccountMetaAllQuery($guid)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('guid = :guid')
            ->setParameter('guid', $guid)
        ;

        return $qb;
    }

    /**
     * Fetches a user's single meta record.
     *
     * @param string $guid
     * @param string $metaName
     *
     * @return Entity\AccountMeta
     */
    public function getAccountMeta($guid, $metaName)
    {
        $query = $this->getAccountMetaQuery($guid, $metaName);

        return $this->findOneWith($query);
    }

    public function getAccountMetaQuery($guid, $metaName)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('guid = :guid')
            ->andWhere('meta = :meta')
            ->setParameter('guid', $guid)
            ->setParameter('meta', $metaName)
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
