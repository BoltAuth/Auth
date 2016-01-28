<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Members event class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MembersEvent extends GenericEvent
{
    /** @var Entity\Account */
    protected $account;
    /** @var array */
    protected $roles;

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
     * @return MembersEvent
     */
    public function setAccount(Entity\Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return MembersEvent
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }
}
