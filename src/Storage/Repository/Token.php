<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Storage\Mapping\Type\CarbonDateTimeType;
use Bolt\Storage\Repository;
use Carbon\Carbon;

/**
 * Token repository.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Token extends AbstractMembersRepository
{
    /**
     * Fetches Token entries by GUID.
     *
     * @param string $token
     *
     * @return Entity\Token[]
     */
    public function getToken($token)
    {
        $query = $this->getTokenQuery($token);

        return $this->findOneWith($query);
    }

    public function getTokenQuery($token)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('token = :token')
            ->setParameter('token', $token)
        ;

        return $qb;
    }

    /**
     * Fetches Token entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Token[]
     */
    public function getTokensByGuid($guid)
    {
        $query = $this->getTokensByGuidQuery($guid);

        return $this->findWith($query);
    }

    public function getTokensByGuidQuery($guid)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('guid = :guid')
            ->setParameter('guid', $guid)
        ;

        return $qb;
    }

    /**
     * Fetches all tokens by cookie.
     *
     * @param $cookie
     *
     * @return Entity\Token[]
     */
    public function getTokensByCookie($cookie)
    {
        $query = $this->getTokensByCookieQuery($cookie);

        return $this->findWith($query);
    }

    public function getTokensByCookieQuery($cookie)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('cookie = :cookie')
            ->setParameter('cookie', $cookie)
        ;

        return $qb;
    }

    /**
     * Fetches expired tokens.
     *
     * @return Entity\Account
     */
    public function getTokensExpired()
    {
        $query = $this->getTokensExpiredQuery();

        return $this->findOneWith($query);
    }

    public function getTokensExpiredQuery()
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('expired < :now')
            ->setParameter('now', Carbon::now(), CarbonDateTimeType::DATETIME)
        ;

        return $qb;
    }
}
