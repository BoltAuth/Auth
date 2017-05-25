<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\InstagramResourceOwner as LeagueInstagramResourceOwner;

/**
 * Instagram ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class InstagramResourceOwner extends LeagueInstagramResourceOwner implements ResourceOwnerInterface
{
    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function getAvatar()
    {
        throw new \Exception('Not yet implemented');
    }
}
