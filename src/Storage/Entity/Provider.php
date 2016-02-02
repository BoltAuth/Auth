<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Provider entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Provider extends AbstractGuidEntity
{
    protected $id;
    protected $provider;
    protected $resource_owner_id;
    protected $refresh_token;
    protected $resource_owner;
    protected $lastupdate;

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = strtolower($provider);
    }

    /**
     * @return string
     */
    public function getResourceOwnerId()
    {
        return $this->resource_owner_id;
    }

    /**
     * @param string $resourceOwnerId
     */
    public function setResourceOwnerId($resourceOwnerId)
    {
        $this->resource_owner_id = $resourceOwnerId;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refresh_token = $refreshToken;
    }

    /**
     * @return array
     */
    public function getResourceOwner()
    {
        return $this->resource_owner;
    }

    /**
     * @param array|ResourceOwnerInterface $resourceOwner
     */
    public function setResourceOwner($resourceOwner)
    {
        if ($resourceOwner instanceof ResourceOwnerInterface) {
            $resourceOwner = $resourceOwner->toArray();
        }
        $this->resource_owner = $resourceOwner;
    }

    /**
     * @return \DateTime
     */
    public function getLastupdate()
    {
        return $this->lastupdate;
    }

    /**
     * @param \DateTime $lastUpdate
     */
    public function setLastupdate($lastUpdate)
    {
        $this->lastupdate = $lastUpdate;
    }
}
