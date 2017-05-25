<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface as LeagueResourceOwnerInterface;

interface ResourceOwnerInterface extends LeagueResourceOwnerInterface
{
    /**
     * Get account name.
     *
     * @return string|null
     */
    public function getName();

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
