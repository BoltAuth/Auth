<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\LinkedInResourceOwner as LeagueLinkedInResourceOwner;

/**
 * LinkedIn ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class LinkedInResourceOwner extends LeagueLinkedInResourceOwner implements ResourceOwnerInterface
{
    /**
     * @inheritDoc
     */
    public function getAvatar()
    {
        return $this->response['pictureUrl'] ?: null;
    }
}
