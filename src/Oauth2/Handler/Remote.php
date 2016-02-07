<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
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
class Remote extends HandlerBase
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
        if (parent::login($request)) {
            return;
        }

        $providerName = $this->providerManager->getProviderName(true);
        $cookie = $request->cookies->get(Session::COOKIE_AUTHORISATION);
        $tokenEntities = $this->records->getTokensByCookie($cookie);

        foreach ($tokenEntities as $tokenEntity) {
            if ($tokenEntity) {
                $provider = $this->records->getProvision($tokenEntity->getGuid(), $providerName);
                if ($provider === false) {
                    continue;
                }
                $oauth = $this->records->getOauthByResourceOwnerId($tokenEntity->getGuid(), $provider->getResourceOwnerId());
                if ($oauth === false) {
                    continue;
                }
                $account = $this->records->getAccountByGuid($tokenEntity->getGuid());
                if ($account !== false) {
                    return;
                }
            }
        }

        $this->getAuthorisationRedirectResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, $grantType = 'authorization_code')
    {
        parent::process($request, $grantType);
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request)
    {
        parent::logout($request);
    }

    protected function getOauthResourceOwner(Request $request)
    {
        //if ($cookie = $request->cookies->get(Types::TOKEN_COOKIE_NAME)) {
        //    $profile = $this->records->getTokensByCookie($cookie);
        //
        //    if (!$profile) {
        //        throw new Exception\AccessDeniedException('No matching profile found.');
        //    } elseif (!$profile['enabled']) {
        //        throw new Exception\AccessDeniedException('Profile disabled.');
        //    }
        //
        //    // Compile the options from the database record.
        //    $options = [
        //        'resource_owner_id' => $profile->getResourceOwnerId(),
        //        'refresh_token'     => $profile->getRefreshToken(),
        //        'access_token'      => $profile->getAccessToken(),
        //        'expires'           => $profile->getExpires(),
        //    ];
        //
        //    // Create and refresh the token
        //    $accessToken = $this->getRefreshToken(new AccessToken($options));
        //    $resourceOwner = $this->provider->getResourceOwner($accessToken);
        //
        //    // Save the new token data
        //    $providerName = $this->providerManager->getProviderName();
        //    $this->records->saveToken($profile);
        //}
    }

    /**
     * Create a redirect response to fetch an authorisation code.
     *
     *
     * @param string $approvalPrompt
     *
     * @throws \RuntimeException
     */
    protected function getAuthorisationRedirectResponse($approvalPrompt = 'auto')
    {
        $providerName = $this->providerManager->getProviderName();
        $provider = $this->providerManager->getProvider($providerName);

        if ($providerName === 'Google' && $approvalPrompt == 'force') {
            /** @var \Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\Google $provider */
            $provider->setAccessType('offline');
        }

        $providerOptions = $this->providerManager->getProviderOptions($providerName);
        $options = array_merge($providerOptions, ['approval_prompt' => $approvalPrompt]);
        $authorizationUrl = $provider->getAuthorizationUrl($options);

        // Get the state generated and store it to the session.
        $this->session->setStateToken($provider);
        $this->setDebugMessage('Storing state token: ' . $provider->getState());

        if ($authorizationUrl == null) {
            throw new \RuntimeException('An error occured with the provider redirect handling.');
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
