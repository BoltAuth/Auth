<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericProvider as LeagueGenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Ramsey\Uuid\Uuid;

/**
 * Local provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Local extends LeagueGenericProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getRequiredOptions()
    {
        // Temporarily fake an access token for Local provider
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($grant, array $options = [])
    {
        if (!isset($options['guid']) || !Uuid::isValid($options['guid'])) {
            throw new \RuntimeException(sprintf('%s requires a "guid" option with a valid v4 UUID.', __METHOD__));
        }

        // Temporarily fake an access token for Local provider.
        $defaultOptions = [
            'access_token'      => Uuid::uuid4()->toString(),
            'resource_owner_id' => $options['guid'],
            'refresh_token'     => Uuid::uuid4()->toString(),
            'expires'           => 86400,
        ];

        return new AccessToken(array_merge($defaultOptions, $options));
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LocalResourceOwner($response, $token->getResourceOwnerId());
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwner(AccessToken $token)
    {
        $response = $this->fetchResourceOwnerDetails($token);

        return $this->createResourceOwner($response, $token);
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return [$token->getResourceOwnerId() => $token->getResourceOwnerId()];
    }
}
