<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Oauth entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Oauth extends AbstractGuidEntity
{
    protected $resource_owner_id;
    protected $password;
    protected $email;
    protected $enabled;

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
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}
