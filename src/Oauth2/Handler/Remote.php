<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Handler;

use Bolt\Extension\BoltAuth\Auth\AccessControl\Session;
use Bolt\Extension\BoltAuth\Auth\Event\AuthEvents;
use Bolt\Extension\BoltAuth\Auth\Exception\DisabledAccountException;
use Bolt\Extension\BoltAuth\Auth\Exception\MissingAccountException;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider\ResourceOwnerInterface;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Request;

/**
 * Remote OAuth2 client login provider.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Remote extends AbstractHandler
{
    /** @var AccessToken */
    protected $accessToken;
    /** @var ResourceOwnerInterface */
    protected $resourceOwner;

    /**
     * {@inheritdoc}
     */
    public function login(Request $request)
    {
        parent::login($request);

        $providerName = $this->providerManager->getProviderName();
        $cookie = $request->cookies->get(Session::COOKIE_AUTHORISATION);
        $tokenEntities = $this->records->getTokensByCookie($cookie);

        /** @var Entity\Token $tokenEntity */
        foreach ((array) $tokenEntities as $tokenEntity) {
            if ($tokenEntity === false) {
                continue;
            }
            $provider = $this->records->getProvision($tokenEntity->getGuid(), $providerName);
            if ($provider === false) {
                continue;
            }
            $oauth = $this->records->getOauthByResourceOwnerId($provider->getResourceOwnerId());
            if ($oauth === false) {
                continue;
            }
            $account = $this->records->getAccountByGuid($tokenEntity->getGuid());
            if ($account !== false) {
                return;
            }
        }

        $this->getAuthorisationRedirectResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, $grantType = 'authorization_code')
    {
        // Check that state token matches the stored one
        $this->session->checkStateToken($request);

        try {
            $this->preProcess($request, $grantType);
            parent::process($request, $grantType);
        } catch (DisabledAccountException $ex) {
            $this->session->addRedirect($this->urlGenerator->generate('authenticationLogin'));
            if ($this->session->getAuthorisation()) {
                $this->dispatchEvent(AuthEvents::AUTH_LOGIN_FAILED_ACCOUNT_DISABLED, $this->session->getAuthorisation());
            }
            $this->feedback->debug(sprintf('Login failed: %s', $ex->getMessage()));

            return;
        } catch (MissingAccountException $e) {
            $this->feedback->debug('No registered account found, redirecting');

            throw $e;
        }

        $this->finish($request);

        return;
    }

    /**
     * @param Request $request
     * @param string  $grantType
     *
     * @throws DisabledAccountException
     * @throws MissingAccountException
     */
    protected function preProcess(Request $request, $grantType)
    {
        $code = $request->query->get('code');
        $options = ['code' => $code];
        $isAuto = $this->config->isRegistrationAutomatic();
        $hasAuth = $this->session->hasAuthorisation();

        try {
            $accessToken = $this->getAccessToken($grantType, $options);
            $resourceOwner = $this->getResourceOwner($accessToken);

            $this->session->setAttribute(Session::SESSION_ATTRIBUTE_OAUTH_DATA, [
                'providerName'  => $this->providerName,
                'accessToken'   => $accessToken,
                'resourceOwner' => $resourceOwner,
            ]);
        } catch (\RuntimeException $e) {
            throw new DisabledAccountException('Exception encountered getting resource owner', $e->getCode(), $e);
        }
        $providerEntity = $this->getProviderEntityByResourceOwnerId($this->providerName, $resourceOwner->getId());

        if ($providerEntity || $isAuto || $hasAuth) {
            $this->setSession($accessToken);

            if ($this->session->isTransitional()) {
                $this->handleAccountTransition($accessToken, $resourceOwner);
            }

            return;
        }
        $redirect = $this->urlGenerator->generate('authProfileRegister');
        $this->session->addRedirect($redirect);

        throw new MissingAccountException();
    }

    /**
     * {@inheritdoc}
     */
    protected function finish(Request $request)
    {
        parent::finish($request);

        $guid = $this->session->getAuthorisation()->getGuid();
        $avatar = $this->resourceOwner->getAvatar();
        if ($avatar === null) {
            return;
        }

        $metaEntity = $this->records->getAccountMeta($guid, 'avatar') ?: new Entity\AccountMeta(['guid' => $guid, 'meta' => 'avatar']);
        if ($metaEntity->getValue() === null) {
            $metaEntity->setValue($avatar);
            $this->records->saveAccountMeta($metaEntity);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request)
    {
        parent::logout($request);
    }

    /**
     * Create a redirect response to fetch an authorisation code.

     * @param string $approvalPrompt
     *
     * @throws \RuntimeException
     */
    protected function getAuthorisationRedirectResponse($approvalPrompt = 'auto')
    {
        $providerName = $this->providerManager->getProviderName();
        $provider = $this->providerManager->getProvider($providerName);

        if ($providerName === 'google' && $approvalPrompt == 'force') {
            /** @var \Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider\Google $provider */
            $provider->setAccessType('offline');
        }

        $providerOptions = $this->providerManager->getProviderOptions($providerName);
        $options = array_merge($providerOptions, ['approval_prompt' => $approvalPrompt]);
        $authorizationUrl = $provider->getAuthorizationUrl($options);

        // Get the state generated and store it to the session.
        $this->session->setStateToken($provider);
        $this->setDebugMessage('Storing state token: ' . $provider->getState());

        if ($authorizationUrl == null) {
            throw new \RuntimeException('An error occurred with the provider redirect handling.');
        }
        $this->session->addRedirect($authorizationUrl);
    }

    /**
     * Get a refresh token from the OAuth provider.
     *
     * @param AccessToken $accessToken
     *
     * @throws IdentityProviderException
     *
     * @return AccessToken
     */
    protected function getRefreshToken(AccessToken $accessToken)
    {
        if ($accessToken->hasExpired()) {
            // Try to get an access token using the authorization code grant.
            $accessToken = $this->provider->getAccessToken('refresh_token', ['refresh_token' => $accessToken->getRefreshToken()]);
        }

        return $accessToken;
    }
}
