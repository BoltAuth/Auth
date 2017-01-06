<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericProvider as LeagueGenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Generic provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Generic extends LeagueGenericProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $token->getResourceOwnerId());
    }
}
