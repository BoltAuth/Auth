<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericProvider as LeagueGenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * WP-OAuth provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class WpOauth extends LeagueGenericProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new WpOauthResourceOwner($response, $token->getResourceOwnerId());
    }
}
