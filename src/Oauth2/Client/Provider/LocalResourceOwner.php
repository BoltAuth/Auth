<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericResourceOwner as LeagueGenericResourceOwner;

/**
 * Local provider ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class LocalResourceOwner extends LeagueGenericResourceOwner implements ResourceOwnerInterface
{
    /**
     * @inheritDoc
     */
    public function getAvatar()
    {
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
    }
}
