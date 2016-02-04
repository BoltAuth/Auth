<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\AccessControl\Role;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Members roles event class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
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
