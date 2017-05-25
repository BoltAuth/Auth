<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\Instagram as LeagueInstagram;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Instagram provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Instagram extends LeagueInstagram
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new InstagramResourceOwner($response);
    }
}
