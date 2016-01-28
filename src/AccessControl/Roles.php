<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Bolt\Extension\Bolt\Members\Event\MembersEvent;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Roles manager.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Roles implements EventSubscriberInterface
{
    /** @var Role[] */
    protected $roles;

    /** @var array */
    private $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
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

    public function addBaseRoles()
    {
        foreach ($this->config['roles']['member'] as $name => $displayName) {
            $this->roles[$name] = new Role($name, $displayName);
        }
    }

    /**
     * Event callback to add additional roles dynamically.
     *
     * @param MembersEvent $event
     */
    public function addRole(MembersEvent $event)
    {
        foreach ((array) $event->getRoles() as $role) {
            if ($role instanceof Role) {
                $this->roles[$role->getName()] = $role;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST      => 'addBaseRoles',
            MembersEvents::MEMBER_ROLE => 'addRole',
        ];
    }
}
