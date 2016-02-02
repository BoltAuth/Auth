<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GithubResourceOwner as LeagueGitHubResourceOwner;

/**
 * GitHub ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GitHubResourceOwner extends LeagueGitHubResourceOwner
{
    /**
     * Get resource avatar URL
     *
     * @return string|null
     */
    public function getImageurl()
    {
        if (!empty($this->response['avatar_url'])) {
            return $this->response['avatar_url'];
        }
    }
}
