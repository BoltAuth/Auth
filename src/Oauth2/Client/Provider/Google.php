<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\Google as LeagueGoogle;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Google provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Google extends LeagueGoogle
{
    /**
     * Sent to Google as the "access_type" parameter.
     *
     * @link https://developers.google.com/accounts/docs/OAuth2WebServer#offline
     *
     * @param string $accessType
     */
    public function setAccessType($accessType)
    {
        $this->accessType = $accessType;
    }

    /**
     * Sent to Google as the "hd" parameter.
     *
     * @link https://developers.google.com/accounts/docs/OAuth2Login#hd-param
     *
     * @param string $hostedDomain
     */
    public function setHostedDomain($hostedDomain)
    {
        $this->hostedDomain = $hostedDomain;
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GoogleResourceOwner($response);
    }
}
