<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Symfony\Component\EventDispatcher\Event;

/**
 * Members profile event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersProfileEvent extends Event
{
    /** @var Entity\Account */
    protected $account;
    /** @var Entity\AccountMeta[] */
    protected $metaEntities;
    /** @var string[] */
    protected $metaEntityNames;

    /**
     * Constructor.
     *
     * @param Entity\Account $account
     */
    public function __construct(Entity\Account $account)
    {
        $this->account = $account;
    }

    /**
     * Return the account entity.
     *
     * @return Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Return the saved meta field entities. Only available on post-save.
     *
     * @return Entity\AccountMeta[]
     */
    public function getMetaEntities()
    {
        return $this->metaEntities;
    }

    /**
     * Add a meta field entity to the event.
     *
     * @internal
     *
     * @param Entity\AccountMeta $metaEntity
     *
     * @return MembersProfileEvent
     */
    public function addMetaEntity(Entity\AccountMeta $metaEntity)
    {
        $fieldName = $metaEntity->getMeta();
        $this->metaEntities[$fieldName] = $metaEntity;

        return $this;
    }

    /**
     * Return the meta field name array.
     *
     * @internal
     *
     * @return array
     */
    public function getMetaEntityNames()
    {
        return (array) $this->metaEntityNames;
    }

    /**
     * Add an array of field names to be used as meta fields.
     *
     * @param array $metaFieldNames
     *
     * @return MembersProfileEvent
     */
    public function addMetaEntryNames(array $metaFieldNames)
    {
        $this->metaEntityNames = array_merge((array) $this->metaEntityNames, $metaFieldNames);

        return $this;
    }
}
