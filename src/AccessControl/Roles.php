<?php

namespace Bolt\Extension\BoltAuth\Auth\AccessControl;

use Bolt\Extension\BoltAuth\Auth\Config\Config;
use Bolt\Extension\BoltAuth\Auth\Event\AuthEvents;
use Bolt\Extension\BoltAuth\Auth\Event\AuthRolesEvent;
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
        foreach ($this->config->getRolesAuth() as $name => $displayName) {
            $this->roles[$name] = new Role($name, $displayName);
        }
    }

    /**
     * Add additional roles dynamically via event.
     */
    protected function buildEventRoles()
    {
        $event = new AuthRolesEvent();
        $this->dispatcher->dispatch(AuthEvents::AUTH_ROLE, $event);

        foreach ((array) $event->getRoles() as $role) {
            if ($role instanceof Role) {
                $this->roles[$role->getName()] = $role;
            }
        }
    }
}
