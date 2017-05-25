<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GithubResourceOwner as LeagueGitHubResourceOwner;

/**
 * GitHub ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GitHubResourceOwner extends LeagueGitHubResourceOwner implements ResourceOwnerInterface
{
    /**
     * @inheritDoc
     */
    public function getAvatar()
    {
        if (empty($this->response['avatar_url'])) {
            return null;
        }

        return $this->response['avatar_url'];
    }
}
