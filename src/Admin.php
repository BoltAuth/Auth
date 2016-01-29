<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Extension\Bolt\Members\Storage\Schema\Table\Account;

/**
 * Members admin class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Admin
{
    /** @var Records */
    protected $records;
    /** @var Config */
    protected $config;

    /**
     * Constructor.
     *
     * @param Records $records
     * @param Config  $config
     */
    public function __construct(Records $records, Config $config)
    {
        $this->records = $records;
        $this->config = $config;
    }

    /**
     * Add a new member account.
     *
     * @param Account $account
     *
     * @return bool
     */
    public function addAccount(Account $account)
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
}
