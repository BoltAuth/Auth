<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\AccessControl\Role;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Members login event class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
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
