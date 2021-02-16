<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Entity;

use PasswordLib\Password\Implementation\Blowfish;

/**
 * Local Oauth entity class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
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
        return $this->getHashedPassword($this->password);
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $this->getHashedPassword($password);
    }

    /**
     * @param string $password
     *
     * @return string|null
     */
    protected function getHashedPassword($password)
    {
        if ($password === null || Blowfish::detect($password)) {
            return $password;
        }

        $password = password_hash($password, PASSWORD_BCRYPT);
        if ($password === false) {
            throw new \RuntimeException('Unable to hash password.');
        }

        return $password;
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
