<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft as LeagueMicrosoft;

/**
 * Microsoft provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Microsoft extends LeagueMicrosoft
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MicrosoftResourceOwner($response);
    }
}
