<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Symfony\Component\EventDispatcher\Event;

/**
 * Members profile event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersProfileEvent extends Event
{
    /** @var Entity\Account */
    protected $account;
    /** @var Entity\AccountMeta */
    protected $metaFields;
    /** @var array */
    protected $metaFieldNames;

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
     * @return Entity\AccountMeta
     */
    public function getMetaFields()
    {
        return $this->metaFields;
    }

    /**
     * Add a meta field entity to the event.
     *
     * @internal
     *
     * @param string             $fieldName
     * @param Entity\AccountMeta $metaField
     *
     * @return MembersProfileEvent
     */
    public function addMetaField($fieldName, Entity\AccountMeta $metaField)
    {
        $this->metaFields[$fieldName] = $metaField;

        return $this;
    }

    /**
     * Return the meta field name array.
     *
     * @internal
     *
     * @return array
     */
    public function getMetaFieldNames()
    {
        return (array) $this->metaFieldNames;
    }

    /**
     * Add an array of field names to be used as meta fields.
     *
     * @param array $metaFieldNames
     *
     * @return MembersProfileEvent
     */
    public function addMetaFieldNames(array $metaFieldNames)
    {
        if ($this->metaFieldNames === null) {
            $this->metaFieldNames = $metaFieldNames;
        } else {
            $this->metaFieldNames = array_merge($this->metaFieldNames, $metaFieldNames);
        }

        return $this;
    }
}
