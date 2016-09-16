<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersRolesEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Roles manager.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Roles
{
    /** @var Role[] */
    protected $roles;

    /** @var Config */
    private $config;
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Config $config, EventDispatcherInterface $dispatcher)
    {
        $this->config = $config;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Add a role.
     *
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $this->roles[$role->getName()] = $role;
    }

    /**
     * Get the defined roles.
     *
     * @return Role[]
     */
    public function getRoles()
    {
        if ($this->roles === null) {
            $this->buildEventRoles();
            $this->buildPriorityRoles();
        }

        return $this->roles;
    }

    /**
     * Add the extension's configured roles.
     */
    protected function buildPriorityRoles()
    {
        foreach ($this->config->getRolesMember() as $name => $displayName) {
            $this->roles[$name] = new Role($name, $displayName);
        }
    }

    /**
     * Add additional roles dynamically via event.
     */
    protected function buildEventRoles()
    {
        $event = new MembersRolesEvent();
        $this->dispatcher->dispatch(MembersEvents::MEMBER_ROLE, $event);

        foreach ((array) $event->getRoles() as $role) {
            if ($role instanceof Role) {
                $this->roles[$role->getName()] = $role;
            }
        }
    }
}
