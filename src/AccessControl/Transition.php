<?php

namespace Bolt\Extension\BoltAuth\Auth\AccessControl;

use Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider\ResourceOwnerInterface;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity;
use League\OAuth2\Client\Token\AccessToken;
use Ramsey\Uuid\Uuid;

/**
 * Provider transition class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Transition
{
    /** @var string */
    protected $guid;
    /** @var AccessToken */
    protected $accessToken;
    /** @var ResourceOwnerInterface */
    protected $resourceOwner;
    /** @var Entity\Provider */
    protected $providerEntity;

    /**
     * Constructor.
     *
     * @param string                 $guid
     * @param string                 $providerName
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     */
    public function __construct($guid, $providerName, AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        if (Uuid::isValid($guid) === false) {
            throw new \RuntimeException('Tried to create Transition object with an invalid GUID.');
        }

        $this->guid = $guid;
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
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @param string $guid
     *
     * @return Transition
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
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
