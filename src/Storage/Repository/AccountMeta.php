<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Repository;

use Bolt\Extension\BoltAuth\Auth\Pager\Pager;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity;

/**
 * Account meta repository.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AccountMeta extends AbstractAuthRepository
{
    const ALIAS = 'm';

    /**
     * Fetches all meta data for an account.
     *
     * @param string $guid
     *
     * @return Entity\AccountMeta[]|false|Pager
     */
    public function getAccountMetaAll($guid)
    {
        $query = $this->getAccountMetaAllQuery($guid);

        if ($this->pagerEnabled) {
            return $this->getPager($query, 'guid');
        }

        $meta = $this->findWith($query) ?: [];

        /** @var Entity\AccountMeta $value */
        foreach ($meta as $key => $value) {
            $meta[$value->getMeta()] = $value;
            unset($meta[$key]);
        }

        return $meta;
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
     * @return Entity\AccountMeta|false
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
     * Fetches all meta data by name and value match.
     *
     * @param string $metaName
     * @param string $metaValue
     *
     * @return Entity\AccountMeta[]
     */
    public function getAccountMetaValues($metaName, $metaValue)
    {
        $query = $this->getAccountMetaValuesQuery($metaName, $metaValue);

        return $this->findWith($query);
    }

    public function getAccountMetaValuesQuery($metaName, $metaValue)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('meta = :meta')
            ->andWhere('value = :value')
            ->setParameter('meta', $metaName)
            ->setParameter('value', $metaValue)
        ;

        return $qb;
    }
}
