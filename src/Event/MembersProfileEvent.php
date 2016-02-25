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
    /** @var array */
    protected $metaFields;

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
     * @return Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return array
     */
    public function getMetaFields()
    {
        return (array) $this->metaFields;
    }

    /**
     * @param array $metaFields
     *
     * @return MembersProfileEvent
     */
    public function setMetaFields(array $metaFields)
    {
        if ($this->metaFields === null) {
            $this->metaFields = $metaFields;
        } else {
            $this->metaFields = array_merge($this->metaFields, $metaFields);
        }

        return $this;
    }
}
