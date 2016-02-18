<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\AccessControl\Role;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Members roles event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersRolesEvent extends GenericEvent
{
    /** @var array */
    protected $roles;

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Role $role
     *
     * @return MembersLoginEvent
     */
    public function addRole(Role $role)
    {
        $this->roles[$role->getName()] = $role;

        return $this;
    }

    /**
     * @param Role[] $roles
     *
     * @return MembersLoginEvent
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }
}
