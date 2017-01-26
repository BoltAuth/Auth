<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericResourceOwner as LeagueGenericResourceOwner;

/**
 * WP-OAuth provider ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class WpOauthResourceOwner extends LeagueGenericResourceOwner
{
    // Seriously the dumbest fucking bullshit I have seen yet
    private $responseResourceOwnerId = 'ID';

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->response[$this->responseResourceOwnerId];
    }
}
