<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Storage\Repository;

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
