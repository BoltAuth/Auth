<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

use Symfony\Component\Validator\Tests\Fixtures\Entity;

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
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = strtolower($provider);
    }

    /**
     * @return mixed
     */
    public function getResourceOwnerId()
    {
        return $this->resource_owner_id;
    }

    /**
     * @param mixed $resource_owner_id
     */
    public function setResourceOwnerId($resource_owner_id)
    {
        $this->resource_owner_id = $resource_owner_id;
    }

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * @param mixed $refresh_token
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = $refresh_token;
    }

    /**
     * @return mixed
     */
    public function getResourceOwner()
    {
        return $this->resource_owner;
    }

    /**
     * @param mixed $resource_owner
     */
    public function setResourceOwner($resource_owner)
    {
        $this->resource_owner = $resource_owner;
    }

    /**
     * @return mixed
     */
    public function getLastupdate()
    {
        return $this->lastupdate;
    }

    /**
     * @param mixed $lastupdate
     */
    public function setLastupdate($lastupdate)
    {
        $this->lastupdate = $lastupdate;
    }
}
