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

    /**
     * Fetches all meta data for an account.
     *
     * @param string $guid
     *
     * @return Entity\AccountMeta[]
     */
    public function getAccountMetaAll($guid)
    {
        return $this->accountMeta->getAccountMetaAll($guid);
    }

    /**
     * Fetches a user's single meta record.
     *
     * @param string $guid
     * @param string $metaName
     *
     * @return Entity\AccountMeta
     */
    public function getAccountMeta($guid, $metaName)
    {
        return $this->accountMeta->getAccountMetaByNameQuery($guid, $metaName);
    }

    /**
     * Fetches an OAuth entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Account
     */
    public function getOauthByGuid($guid)
    {
        return $this->oauth->getOauthByGuidQuery($guid);
    }

    /**
     * Fetches an OAuth entries by GUID.
     *
     * @param string $email
     *
     * @return Entity\Account
     */
    public function getOauthByEmail($email)
    {
        return $this->oauth->getOauthByEmailQuery($email);
    }

    /**
     * Fetches an OAuth entries by Resource Owner ID.
     *
     * @param string $resourceOwnerId
     *
     * @return Entity\Account
     */
    public function getOauthByResourceOwnerId($resourceOwnerId)
    {
        return $this->oauth->getOauthByResourceOwnerIdQuery($resourceOwnerId);
    }

    /**
     * Fetches Provider entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Provider[]
     */
    public function getProvisionsByGuid($guid)
    {
        return $this->provider->getProvisionsByGuidQuery($guid);
    }

    /**
     * Fetches Provider entries by provider name.
     *
     * @param string $provider
     *
     * @return Entity\Provider[]
     */
    public function getProvisionsByProvider($provider)
    {
        return $this->provider->getProvisionsByProviderQuery($provider);
    }

    /**
     * Fetches Provider entry by resource owner.
     *
     * @param string $resourceOwner
     *
     * @return Entity\Provider
     */
    public function getProvisionByResourceOwner($resourceOwner)
    {
        return $this->provider->getProvisionByResourceOwnerQuery($resourceOwner);
    }

    /**
     * Fetches an Provider entries by resource owner ID.
     *
     * @param string $resourceOwnerId
     *
     * @return Entity\Provider
     */
    public function getProvisionByResourceOwnerId($resourceOwnerId)
    {
        return $this->provider->getProvisionByResourceOwnerIdQuery($resourceOwnerId);
    }
}
