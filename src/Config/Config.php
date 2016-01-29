<?php

namespace Bolt\Extension\Bolt\Members\Config;

use Bolt\Helpers\Arr;

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
    protected $urlAuthenticate;
    /** @var string */
    protected $urlMembers;

    /**
     * Constructor.
     *
     * @param $extensionConfig
     */
    public function __construct(array $extensionConfig)
    {
        $config = Arr::mergeRecursiveDistinct($this->getDefaultConfig(), $extensionConfig);

        $this->registration = $config['registration'];
        $this->rolesAdmin = $config['roles']['admin'];
        $this->rolesMember = $config['roles']['member'];
        $this->urlAuthenticate = $config['urls']['authenticate'];
        $this->urlMembers = $config['urls']['members'];
    }

    /**
     * @return string
     */
    public function getUrlAuthenticate()
    {
        return $this->urlAuthenticate;
    }

    /**
     * @param string $urlAuthenticate
     *
     * @return Config
     */
    public function setUrlAuthenticate($urlAuthenticate)
    {
        $this->urlAuthenticate = $urlAuthenticate;

        return $this;
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
        return $this->urlAuthenticate;
    }

    /**
     * @param string $urlAuthenticate
     *
     * @return Config
     */
    public function setUrlAuthentication($urlAuthenticate)
    {
        $this->urlAuthenticate = $urlAuthenticate;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlMembers()
    {
        return $this->urlMembers;
    }

    /**
     * @param string $urlMembers
     *
     * @return Config
     */
    public function setUrlMembers($urlMembers)
    {
        $this->urlMembers = $urlMembers;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'registration' => true,
            'roles' => [
                'admin'  => [
                    'root'
                ],
                'member' => [
                    'admin' => 'Administrator',
                ],
            ],
            'urls'         => [
                'authenticate' => 'authentication',
                'members'      => 'membership',
            ],
        ];
    }
}
