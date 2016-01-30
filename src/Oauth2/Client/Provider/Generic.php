<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericProvider as LeagueGenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Generic provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Generic extends LeagueGenericProvider
{
    /**
     * Constructor.
     *
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (empty($options)) {
            $options = [
                'urlAuthorize'            => 'invalid',
                'urlAccessToken'          => 'invalid',
                'urlResourceOwnerDetails' => 'invalid',
            ];
        }

        parent::__construct($options, $collaborators);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $token->getResourceOwnerId());
    }
}
