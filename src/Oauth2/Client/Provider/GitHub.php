<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\Github as LeagueGitHub;
use League\OAuth2\Client\Token\AccessToken;

/**
 * GitHub provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GitHub extends LeagueGitHub
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GitHubResourceOwner($response);
    }
}
