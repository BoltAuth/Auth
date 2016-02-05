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
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return \DateTime
     */
    public function getLastseen()
    {
        return $this->lastseen;
    }

    /**
     * @param \DateTime $lastseen
     */
    public function setLastseen($lastseen)
    {
        $this->lastseen = $lastseen;
    }

    /**
     * @return string
     */
    public function getLastip()
    {
        return $this->lastip;
    }

    /**
     * @param string $lastip
     */
    public function setLastip($lastip)
    {
        $this->lastip = $lastip;
    }

    /**
     * @return string
     */
    public function getDisplayname()
    {
        return $this->displayname;
    }

    /**
     * @param string $displayname
     */
    public function setDisplayname($displayname)
    {
        $this->displayname = $displayname;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
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

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }
}
