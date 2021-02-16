<?php

namespace Bolt\Extension\BoltAuth\Auth\Event;

use Bolt\Extension\BoltAuth\Auth\AccessControl\Role;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Auth roles event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthRolesEvent extends GenericEvent
{
    /** @var Role[] */
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
     * @return AuthRolesEvent
     */
    public function addRole(Role $role)
    {
        $this->roles[$role->getName()] = $role;

        return $this;
    }

    /**
     * @param Role[] $roles
     *
     * @return AuthRolesEvent
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }
}
