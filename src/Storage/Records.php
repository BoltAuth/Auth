<?php

namespace Bolt\Extension\Bolt\Members\Storage;

/**
 * Membership records.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Records
{
    /** @var Repository\Account */
    protected $account;
    /** @var Repository\AccountMeta */
    protected $accountMeta;
    /** @var Repository\Oauth */
    protected $oauth;
    /** @var Repository\Provider */
    protected $provider;
    /** @var Repository\Token */
    protected $token;

    /**
     * Constructor.
     *
     * @param Repository\Account     $account
     * @param Repository\AccountMeta $accountMeta
     * @param Repository\Oauth       $oauth
     * @param Repository\Provider    $provider
     * @param Repository\Token       $token
     */
    public function __construct(
        Repository\Account $account,
        Repository\AccountMeta $accountMeta,
        Repository\Oauth $oauth,
        Repository\Provider $provider,
        Repository\Token $token
    ) {
        $this->account = $account;
        $this->accountMeta = $accountMeta;
        $this->oauth = $oauth;
        $this->provider = $provider;
        $this->token = $token;
    }

    /**
     * Get all membership accounts
     *
     * @return Entity\Account
     */
    public function getAccounts()
    {
        return $this->account->getAccounts();
    }

    /**
     * Fetches an account by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Account
     */
    public function getAccountByGuid($guid)
    {
        return $this->account->getAccountByGuid($guid);
    }

    /**
     * Fetches an account by email.
     *
     * @param string $email
     *
     * @return Entity\Account
     */
    public function getAccountByEmail($email)
    {
        return $this->account->getAccountByEmail($email);
    }

    /**
     * Fetches an account by account name.
     *
     * @param string $username
     *
     * @return Entity\Account
     */
    public function getAccountByUserName($username)
    {
        return $this->account->getAccountByUserName($username);
    }

    /**
     * Returns all accounts that are either enabled, or disabled.
     *
     * @param boolean $status
     *
     * @return Entity\Account[]
     */
    public function getAccountsByEnableStatus($status)
    {
        return $this->account->getAccountsByEnableStatus($status);
    }
}
