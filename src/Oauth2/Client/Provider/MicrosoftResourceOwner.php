<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider;

use Stevenmaguire\OAuth2\Client\Provider\MicrosoftResourceOwner as LeagueMicrosoftResourceOwner;

/**
 * Microsoft ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MicrosoftResourceOwner extends LeagueMicrosoftResourceOwner implements ResourceOwnerInterface
{
    /**
     * @inheritDoc
     */
    public function getAvatar()
    {
        return $this->imageurl;
    }
}
