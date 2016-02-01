<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Storage\Entity\AbstractGuidEntity;
use Bolt\Storage\Entity\Entity;
use Bolt\Storage\QuerySet;
use Bolt\Storage\Repository;
use Ramsey\Uuid\Uuid;

/**
 * Base repository for Members.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractMembersRepository extends Repository
{
    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias = null)
    {
        return $this->em->createQueryBuilder()
            ->from($this->getTableName());
    }
}
