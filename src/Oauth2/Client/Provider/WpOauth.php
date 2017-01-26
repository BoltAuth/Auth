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
        if (!isset($response['ID'])) {
            throw new \RuntimeException('Please ask your OAuth provider to uninstall WP-OAuth as it breaks specification, is completely insecure, and has a list of CVEs longer than the platform itself.');
        }

        return new WpOauthResourceOwner($response, $response['ID']);
    }
}
