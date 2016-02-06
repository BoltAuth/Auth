<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

/**
 * Local Oauth entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Oauth extends AbstractGuidEntity
{
    /** @var integer */
    protected $id;
    /** @var string */
    protected $resource_owner_id;
    /** @var string */
    protected $password;
    /** @var boolean */
    protected $enabled;

    /**
     * @return string
     */
    public function getResourceOwnerId()
    {
        return $this->resource_owner_id;
    }

    /**
     * @param string $resource_owner_id
     */
    public function setResourceOwnerId($resource_owner_id)
    {
        $this->resource_owner_id = $resource_owner_id;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}
