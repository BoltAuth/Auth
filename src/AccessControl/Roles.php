<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersRolesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Roles manager.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Roles implements EventSubscriberInterface
{
    /** @var Role[] */
    protected $roles;

    /** @var Config */
    private $config;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get the defined roles.
     *
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add the extension's configured roles.
     */
    public function addBaseRoles()
    {
        foreach ($this->config->getRolesMember() as $name => $displayName) {
            $this->roles[$name] = new Role($name, $displayName);
        }
    }

    /**
     * Event callback to add additional roles dynamically.
     *
     * @param MembersRolesEvent $event
     */
    public function addCustomRoles(MembersRolesEvent $event)
    {
        foreach ((array) $event->getRoles() as $role) {
            if ($role instanceof Role) {
                $this->roles[$role->getName()] = $role;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MembersEvents::MEMBER_ROLE => [
                ['addBaseRoles', -512],
                ['addCustomRoles', -512],
            ],
        ];
    }
}
