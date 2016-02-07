<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Controller\Zone;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Users;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Members admin class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Admin implements EventSubscriberInterface
{
    /** @var Records */
    protected $records;
    /** @var Config */
    protected $config;
    /** @var Users */
    private $users;

    /**
     * Constructor.
     *
     * @param Records $records
     * @param Config  $config
     */
    public function __construct(Records $records, Config $config, Users $users)
    {
        $this->records = $records;
        $this->config = $config;
        $this->users = $users;
    }

    /**
     * Add a new member account.
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
     * Delete a member account.
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
     * Enable a member account.
     *
     * @param string $guid
     *
     * @return bool
     */
    public function enableAccount($guid)
    {
        $account = $this->records->getAccountByGuid($guid);
        $account->setEnabled(true);

        return $this->records->saveAccount($account);
    }

    /**
     * Disable a member account.
     *
     * @param string $guid
     *
     * @return bool
     */
    public function disableAccount($guid)
    {
        $account = $this->records->getAccountByGuid($guid);
        $account->setEnabled(false);

        return $this->records->saveAccount($account);
    }

    /**
     * Add a new member account.
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
     * Delete a member account.
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
     * Enforce system roles for runtime modification of member properties.
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
