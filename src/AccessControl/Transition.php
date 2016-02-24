<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\ResourceOwnerInterface;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Provider transition class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Transition
{
    /** @var AccessToken */
    protected $accessToken;
    /** @var ResourceOwnerInterface */
    protected $resourceOwner;
    /** @var Entity\Provider */
    protected $providerEntity;

    /**
     * Constructor.
     *
     * @param string                 $providerName
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     */
    public function __construct($providerName, AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        $this->accessToken = $accessToken;
        $this->resourceOwner = $resourceOwner;

        $providerEntity = new Entity\Provider();
        $providerEntity->setProvider($providerName);
        $providerEntity->setRefreshToken($accessToken->getRefreshToken());
        $providerEntity->setResourceOwnerId($resourceOwner->getId());
        $providerEntity->setResourceOwner($resourceOwner);
        $this->providerEntity = $providerEntity;
    }

    /**
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param AccessToken $accessToken
     *
     * @return Transition
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner()
    {
        return $this->resourceOwner;
    }

    /**
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return Transition
     */
    public function setResourceOwner($resourceOwner)
    {
        $this->resourceOwner = $resourceOwner;

        return $this;
    }

    /**
     * @return Entity\Provider
     */
    public function getProviderEntity()
    {
        return $this->providerEntity;
    }

    /**
     * @param Entity\Provider $providerEntity
     *
     * @return Transition
     */
    public function setProviderEntity($providerEntity)
    {
        $this->providerEntity = $providerEntity;

        return $this;
    }
}
