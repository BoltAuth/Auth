<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Account entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Account extends AbstractGuidEntity
{
    protected $username;
    protected $email;
    protected $lastseen;
    protected $lastip;
    protected $displayname;
    protected $enabled;
    protected $roles;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->guid;
    }

    /**
     * @param string $guid
     */
    public function setId($guid)
    {
        $this->guid = $guid;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
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
    public function getLastseen()
    {
        return $this->lastseen;
    }

    /**
     * @param mixed $lastseen
     */
    public function setLastseen($lastseen)
    {
        $this->lastseen = $lastseen;
    }

    /**
     * @return mixed
     */
    public function getLastip()
    {
        return $this->lastip;
    }

    /**
     * @param mixed $lastip
     */
    public function setLastip($lastip)
    {
        $this->lastip = $lastip;
    }

    /**
     * @return mixed
     */
    public function getDisplayname()
    {
        return $this->displayname;
    }

    /**
     * @param mixed $displayname
     */
    public function setDisplayname($displayname)
    {
        $this->displayname = $displayname;
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

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }
}
