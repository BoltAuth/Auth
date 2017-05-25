<?php

namespace Bolt\Extension\BoltAuth\Auth\Admin;

use Bolt\Controller\Zone;
use Bolt\Extension\BoltAuth\Auth\Config\Config;
use Bolt\Extension\BoltAuth\Auth\Event\AuthEvents;
use Bolt\Extension\BoltAuth\Auth\Event\AuthProfileEvent;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity;
use Bolt\Extension\BoltAuth\Auth\Storage\Records;
use Bolt\Users;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Auth admin class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Manager implements EventSubscriberInterface
{
    /** @var Records */
    protected $records;
    /** @var Config */
    protected $config;
    /** @var Users */
    private $users;
    /** @var EventDispatcherInterface  */
    protected $dispatcher;

    /**
     * Constructor.
     *
     * @param Records                  $records
     * @param Config                   $config
     * @param Users                    $users
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Records $records, Config $config, Users $users, EventDispatcherInterface $dispatcher)
    {
        $this->records = $records;
        $this->config = $config;
        $this->users = $users;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Add a new auth account.
     *
     * @param Entity\Account $account
     *
     * @return bool
     */
    public function addAccount(Entity\Account $account)
    {
        return $this->records->saveAccount($account);
    }

    /**
     * Delete a auth account.
     *
     * @param string $guid
     *
     * @return bool
     */
    public function deleteAccount($guid)
    {
        return $this->records->deleteAccount($this->records->getAccountByGuid($guid));
    }

    /**
     * Enable a auth account.
     *
     * @param string $guid
     *
     * @return bool
     */
    public function enableAccount($guid)
    {
        $account = $this->records->getAccountByGuid($guid);
        $account->setEnabled(true);
        $event = new AuthProfileEvent($account);
        $this->dispatcher->dispatch(AuthEvents::AUTH_ENABLE, $event);

        return $this->records->saveAccount($account);
    }

    /**
     * Disable a auth account.
     *
     * @param string $guid
     *
     * @return bool
     */
    public function disableAccount($guid)
    {
        $account = $this->records->getAccountByGuid($guid);
        $account->setEnabled(false);
        $event = new AuthProfileEvent($account);
        $this->dispatcher->dispatch(AuthEvents::AUTH_DISABLE, $event);

        return $this->records->saveAccount($account);
    }

    /**
     * Add a new auth account.
     *
     * @param string $guid
     * @param string $role
     *
     * @return bool
     */
    public function addAccountRole($guid, $role)
    {
        $account = $this->records->getAccountByGuid($guid);
        $roles = $account->getRoles();
        if (!in_array($role, (array) $roles)) {
            $roles[] = $role;
        }
        $account->setRoles($roles);

        return $this->records->saveAccount($account);
    }

    /**
     * Delete a auth account.
     *
     * @param string $guid
     * @param string $role
     *
     * @return bool
     */
    public function deleteAccountRole($guid, $role)
    {
        $account = $this->records->getAccountByGuid($guid);
        $roles = array_filter(
            (array) $account->getRoles(),
            function ($r) use ($role) {
                return $r !== $role ?: false;
            }
        );
        $account->setRoles($roles);

        return $this->records->saveAccount($account);
    }

    /***
     * Enforce system roles for runtime modification of auth properties.
     *
     * @param GetResponseEvent $event
     *
     * @throws AccessDeniedException
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!Zone::isBackend($event->getRequest())) {
            return;
        }

        foreach ($this->config->getRolesAdmin() as $role) {
            if ($this->users->isAllowed($role)) {
                return;
            }
        }

        throw new AccessDeniedException('Logged in user does not have the correct rights to use this class.');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [];
    }
}
