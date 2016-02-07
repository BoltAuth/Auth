<?php

namespace Bolt\Extension\Bolt\Members\Storage;

/**
 * Membership records.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
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
     * Save an account entity.
     *
     * @param Entity\Account $account
     *
     * @return bool
     */
    public function saveAccount(Entity\Account $account)
    {
        return $this->account->save($account);
    }

    /**
     * Delete an account entity.
     *
     * @param Entity\Account $account
     *
     * @return bool
     */
    public function deleteAccount(Entity\Account $account)
    {
        return $this->account->delete($account);
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
        return $this->accountMeta->getAccountMeta($guid, $metaName);
    }

    /**
     * Save an account meta entity.
     *
     * @param Entity\AccountMeta $accountMeta
     *
     * @return bool
     */
    public function saveAccountMeta(Entity\AccountMeta $accountMeta)
    {
        return $this->accountMeta->save($accountMeta);
    }

    /**
     * Fetches an OAuth entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Oauth
     */
    public function getOauthByGuid($guid)
    {
        return $this->oauth->getOauthByGuid($guid);
    }

    /**
     * Fetches an OAuth entries by Resource Owner ID.
     *
     * @param string $resourceOwnerId
     *
     * @return Entity\Oauth
     */
    public function getOauthByResourceOwnerId($resourceOwnerId)
    {
        return $this->oauth->getOauthByResourceOwnerId($resourceOwnerId);
    }

    /**
     * Save an OAuth entity.
     *
     * @param Entity\Oauth $oauth
     *
     * @return bool
     */
    public function saveOauth(Entity\Oauth $oauth)
    {
        return $this->oauth->save($oauth);
    }

    /**
     * Fetches Provider entries by GUID.
     *
     * @param string $guid
     * @param string $provider
     *
     * @return Entity\Provider
     */
    public function getProvision($guid, $provider)
    {
        return $this->provider->getProvision($guid, $provider);
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
        return $this->provider->getProvisionsByGuid($guid);
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
        return $this->provider->getProvisionsByProvider($provider);
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
        return $this->provider->getProvisionByResourceOwner($resourceOwner);
    }

    /**
     * Fetches an Provider entries by resource owner ID.
     *
     * @param string $provider
     * @param string $resourceOwnerId
     *
     * @return Entity\Provider
     */
    public function getProvisionByResourceOwnerId($provider, $resourceOwnerId)
    {
        return $this->provider->getProvisionByResourceOwnerId($provider, $resourceOwnerId);
    }

    /**
     * Save a provider entity.
     *
     * @param Entity\Provider $provider
     *
     * @return bool
     */
    public function saveProvider(Entity\Provider $provider)
    {
        return $this->provider->save($provider);
    }

    /**
     * Fetches Token entries by GUID.
     *
     * @param string $guid
     *
     * @return Entity\Token[]
     */
    public function getTokensByGuid($guid)
    {
        return $this->token->getTokensByGuid($guid);
    }

    /**
     * Fetches all tokens by cookie.
     *
     * @param $cookie
     *
     * @return Entity\Token[]
     */
    public function getTokensByCookie($cookie)
    {
        return $this->token->getTokensByCookie($cookie);
    }

    /**
     * Fetches expired tokens.
     *
     * @return Entity\Token[]
     */
    public function getTokensExpired()
    {
        return $this->token->getTokensExpired();
    }

    /**
     * Delete a token entity.
     *
     * @param Entity\Token $token
     *
     * @return bool
     */
    public function deleteToken(Entity\Token $token)
    {
        return $this->token->delete($token);
    }

    /**
     * Save a token entity.
     *
     * @param Entity\Token $token
     *
     * @return bool
     */
    public function saveToken(Entity\Token $token)
    {
        return $this->token->save($token);
    }
}
