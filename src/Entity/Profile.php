<?php

namespace Bolt\Extension\Bolt\Members\Entity;

use Bolt\Extension\Bolt\Members\Records;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User profile object class
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Profile
{
    /** @var string */
    protected $guid;
    /** @var string */
    protected $userName;
    /** @var string */
    protected $email;
    /** @var string */
    protected $lastSeen;
    /** @var string */
    protected $lastIp;
    /** @var string */
    protected $displayName;
    /** @var bool */
    protected $enabled;
    /** @var array */
    protected $roles;
    /** @var array */
    protected $meta;
    /** @var Records */
    private $records;

    /**
     * @param \Silex\Application $app
     */
    public function __construct(array $member, Records $records)
    {
        $this->$records = $records;

        $this->guid = $member['guid'];
        $this->userName = $member['username'];
        $this->email = $member['email'];
        $this->lastSeen = $member['lastseen'];
        $this->lastIp = $member['lastip'];
        $this->displayName = $member['displayname'];
        $this->enabled = $member['enabled'];
        $this->roles = $member['roles'];
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
     * @return Profile
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     *
     * @return Profile
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
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
     *
     * @return Profile
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastSeen()
    {
        return $this->lastSeen;
    }

    /**
     * @param string $lastSeen
     *
     * @return Profile
     */
    public function setLastSeen($lastSeen)
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastIp()
    {
        return $this->lastIp;
    }

    /**
     * @param string $lastIp
     *
     * @return Profile
     */
    public function setLastIp($lastIp)
    {
        $this->lastIp = $lastIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     *
     * @return Profile
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
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
     *
     * @return Profile
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return json_decode($this->roles, true);
    }

    /**
     * @param array $roles
     *
     * @return Profile
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return array
     */
    public function getMeta($cache = true)
    {
        if ($cache && $this->meta !== null) {
            return $this->meta;
        }

        return $this->meta = $this->records->getMemberMeta($this->guid);
    }

    /**
     * @param array $meta
     *
     * @return Profile
     */
    //public function setMeta($meta)
    //{
    //    $this->meta = $meta;
    //
    //    return $this;
    //}

    /**
     * Get a member's profile
     *
     * @param integer $userId
     *
     * @return array
     */
    public function getMembersProfile($userId)
    {
        /** @var Records $records */
        $records = $this->app['members.records'];
        $member = $records->getMember('id', $userId);

        if ($member) {
            $member['avatar']   = $records->getMemberMetaValue($userId, 'avatar');
            $member['location'] = $records->getMemberMetaValue($userId, 'location');

            return $member;
        }

        return $this->getDeletedUser();
    }

    /**
     * Get a default array to account for a deleted user's data
     *
     * @return array
     */
    private function getDeletedUser()
    {
        return [
            'id'          => -1,
            'username'    => 'deleted',
            'email'       => '',
            'displayname' => 'Deleted User',
            'lastseen'    => '0000-00-00 00:00:00',
            'lastip'      => '',
            'enabled'     => 0,
            'roles'       => '',
            'avatar'      => 'http://placehold.it/350x150&text=Deleted+User',
            'location'    => 'Unknown',
        ];
    }
}
