<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage;

use Carbon\Carbon;
use Pimple as Container;

/**
 * Auth records.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Records
{
    /** @var Container */
    private $repositories;

    /**
     * Constructor.
     *
     * @param Container $repositories
     */
    public function __construct(Container $repositories)
    {
        $this->repositories = $repositories;
    }

    /**
     * Create and store the account record.
     *
     * @param string $displayName
     * @param string $emailAddress
     * @param array  $roles
     *
     * @return Entity\Account|false
     */
    public function createAccount($displayName, $emailAddress, array $roles)
    {
        $account = new Entity\Account();
        $account->setDisplayname($displayName);
        $account->setEmail($emailAddress);
        $account->setRoles($roles);
        $account->setEnabled(true);
        $account->setVerified(false);

        return $this->getAccountRepository()->save($account) ? $account : false;
    }

    /**
     * Fetch all auth accounts.
     *
     * @param string $orderBy
     * @param string $order
     *
     * @return Entity\Account[]
     */
    public function getAccounts($orderBy = 'displayname', $order = null)
    {
        return $this->getAccountRepository()->getAccounts($orderBy, $order);
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
        return $this->getAccountRepository()->getAccountByGuid($guid);
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
        return $this->getAccountRepository()->getAccountByEmail($email);
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
        return $this->getAccountRepository()->getAccountByUserName($username);
    }

    /**
     * Fetches an account by meta key/value.
     *
     * @param string $guid
     * @param string $meta
     * @param string $value
     *
     * @return Entity\Account
     */
    public function getAccountByMeta($guid, $meta, $value)
    {
        return $this->getAccountRepository()->getAccountByMeta($guid, $meta, $value);
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
        return $this->getAccountRepository()->getAccountsByEnableStatus($status);
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
        return $this->getAccountRepository()->save($account);
    }

    /**
     * Search for auth accounts.
     *
     * @param string $term
     * @param string $orderBy
     * @param null   $order
     *
     * @return array|\Bolt\Extension\BoltAuth\Auth\Pager\Pager
     */
    public function searchAccount($term, $orderBy, $order)
    {
        return $this->getAccountRepository()->search($term, $orderBy, $order);
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
        return $this->getAccountRepository()->delete($account);
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
        return $this->getAccountMetaRepository()->getAccountMetaAll($guid);
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
        return $this->getAccountMetaRepository()->getAccountMeta($guid, $metaName);
    }

    /**
     * Fetches all meta data by name and value match.
     *
     * @param string $metaName
     * @param string $metaValue
     *
     * @return Entity\AccountMeta[]
     */
    public function getAccountMetaValues($metaName, $metaValue)
    {
        return $this->getAccountMetaRepository()->getAccountMetaValues($metaName, $metaValue);
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
        return $this->getAccountMetaRepository()->save($accountMeta);
    }

    /**
     * Delete an account meta entity.
     *
     * @param Entity\AccountMeta $accountMeta
     *
     * @return bool
     */
    public function deleteAccountMeta(Entity\AccountMeta $accountMeta)
    {
        return $this->getAccountMetaRepository()->delete($accountMeta);
    }

    /**
     * @param string $guid
     * @param string $resourceOwnerId
     * @param bool   $enabled
     *
     * @return Entity\Oauth
     */
    public function createOauth($guid, $resourceOwnerId, $enabled = true)
    {
        $oauth = new Entity\Oauth();
        $oauth->setGuid($guid);
        $oauth->setResourceOwnerId($resourceOwnerId);
        $oauth->setEnabled($enabled);

        $this->getOauthRepository()->save($oauth);

        return $oauth;
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
        return $this->getOauthRepository()->getOauthByGuid($guid);
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
        return $this->getOauthRepository()->getOauthByResourceOwnerId($resourceOwnerId);
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
        return $this->getOauthRepository()->save($oauth);
    }

    /**
     * Create and store a new provider.
     *
     * @param string $guid
     * @param string $providerName
     * @param string $resourceOwnerId
     *
     * @return Entity\Provider
     */
    public function createProviderEntity($guid, $providerName, $resourceOwnerId)
    {
        $provider = new Entity\Provider();
        $provider->setGuid($guid);
        $provider->setProvider($providerName);
        $provider->setResourceOwnerId($resourceOwnerId);
        $provider->setLastupdate(Carbon::now());

        $this->getProviderRepository()->save($provider);

        return $provider;
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
        return $this->getProviderRepository()->getProvision($guid, $provider);
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
        return $this->getProviderRepository()->getProvisionsByGuid($guid);
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
        return $this->getProviderRepository()->getProvisionsByProvider($provider);
    }

    /**
     * Fetches Provider entry by resource owner.
     *
     * @param string $resourceOwner
     *
     * @return Entity\Provider[]
     */
    public function getProvisionByResourceOwner($resourceOwner)
    {
        return $this->getProviderRepository()->getProvisionByResourceOwner($resourceOwner);
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
        return $this->getProviderRepository()->getProvisionByResourceOwnerId($provider, $resourceOwnerId);
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
        return $this->getProviderRepository()->save($provider);
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
        return $this->getTokenRepository()->getTokensByGuid($guid);
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
        return $this->getTokenRepository()->getTokensByCookie($cookie);
    }

    /**
     * Fetches expired tokens.
     *
     * @return Entity\Token[]
     */
    public function getTokensExpired()
    {
        return $this->getTokenRepository()->getTokensExpired();
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
        return $this->getTokenRepository()->delete($token);
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
        return $this->getTokenRepository()->save($token);
    }

    /**
     * @return Repository\Account
     */
    protected function getAccountRepository()
    {
        return $this->repositories['account'];
    }

    /**
     * @return Repository\AccountMeta
     */
    protected function getAccountMetaRepository()
    {
        return $this->repositories['account_meta'];
    }

    /**
     * @return Repository\Oauth
     */
    protected function getOauthRepository()
    {
        return $this->repositories['oauth'];
    }

    /**
     * @return Repository\Provider
     */
    protected function getProviderRepository()
    {
        return $this->repositories['provider'];
    }

    /**
     * @return Repository\Token
     */
    protected function getTokenRepository()
    {
        return $this->repositories['token'];
    }
}
