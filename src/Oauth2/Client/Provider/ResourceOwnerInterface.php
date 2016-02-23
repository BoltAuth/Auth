<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface as LeagueResourceOwnerInterface;

interface ResourceOwnerInterface extends LeagueResourceOwnerInterface
{
    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Get avatar image URL.
     *
     * @return string|null
     */
    public function getAvatar();
}
