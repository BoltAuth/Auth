<?php

namespace Bolt\Extension\Bolt\Members\Config;

/**
 * Base configuration class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Config
{
    /** @var  boolean */
    protected $registration;
    /** @var array */
    protected $rolesAdmin;
    /** @var array */
    protected $rolesMember;
    /** @var string */
    protected $urlAuthentication;
    /** @var string */
    protected $urlMembership;

    /**
     * Constructor.
     *
     * @param $extensionConfig
     */
    public function __construct(array $extensionConfig)
    {
        $this->registration = $extensionConfig['registration'];
        $this->rolesAdmin = $extensionConfig['roles']['admin'];
        $this->rolesMember = $extensionConfig['roles']['member'];
        $this->urlAuthentication = $extensionConfig['urls']['authentication'];
        $this->urlMembership = $extensionConfig['urls']['membership'];
    }

    /**
     * @return boolean
     */
    public function isRegistrationOpen()
    {
        return $this->registration;
    }

    /**
     * @param boolean $registration
     *
     * @return Config
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * @return array
     */
    public function getRolesAdmin()
    {
        return (array) $this->rolesAdmin;
    }

    /**
     * @param array $rolesAdmin
     *
     * @return Config
     */
    public function setRolesAdmin(array $rolesAdmin)
    {
        $this->rolesAdmin = $rolesAdmin;

        return $this;
    }

    /**
     * @return array
     */
    public function getRolesMember()
    {
        return (array) $this->rolesMember;
    }

    /**
     * @param array $rolesMember
     *
     * @return Config
     */
    public function setRolesMember(array $rolesMember)
    {
        $this->rolesMember = $rolesMember;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlAuthentication()
    {
        return $this->urlAuthentication;
    }

    /**
     * @param string $urlAuthentication
     *
     * @return Config
     */
    public function setUrlAuthentication($urlAuthentication)
    {
        $this->urlAuthentication = $urlAuthentication;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlMembership()
    {
        return $this->urlMembership;
    }

    /**
     * @param string $urlMembership
     *
     * @return Config
     */
    public function setUrlMembership($urlMembership)
    {
        $this->urlMembership = $urlMembership;

        return $this;
    }
}
