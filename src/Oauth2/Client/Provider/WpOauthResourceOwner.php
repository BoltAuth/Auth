<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericResourceOwner as LeagueGenericResourceOwner;

/**
 * WP-OAuth provider ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class WpOauthResourceOwner extends LeagueGenericResourceOwner implements ResourceOwnerInterface
{
    // Dumb implementation detail of WPOauth, necessitating special workaround.
    private $responseResourceOwnerId = 'ID';

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->response[$this->responseResourceOwnerId];
    }

    /**
     * Get user name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['display_name'] ?: null;
    }

    /**
     * @inheritDoc
     */
    public function getAvatar()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->response['email'];
    }
}
