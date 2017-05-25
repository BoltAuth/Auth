<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Members login event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersLoginEvent extends GenericEvent
{
    /** @var Entity\Account */
    protected $account;

    /**
     * @return Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Entity\Account $account
     *
     * @return MembersLoginEvent
     */
    public function setAccount(Entity\Account $account)
    {
        $this->account = $account;

        return $this;
    }
}
