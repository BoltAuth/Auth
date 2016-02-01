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
    /** @var array */
    protected $addOns;
    /** @var array */
    protected $labels;
    /** @var Provider[] */
    protected $providers;
    /** @var  boolean */
    protected $registration;
    /** @var array */
    protected $rolesAdmin;
    /** @var array */
    protected $rolesMember;
    /** @var array */
    protected $rolesRegister;
    /** @var array */
    protected $templates;
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

        $this->addOns =  $config['addons'];
        $this->labels =  $config['labels'];
        $this->registration = $config['registration'];
        $this->rolesAdmin = $config['roles']['admin'];
        $this->rolesMember = $config['roles']['member'];
        $this->rolesRegister = $config['roles']['register'];
        $this->templates = $config['templates'];
        $this->urlAuthenticate = $config['urls']['authenticate'];
        $this->urlMembers = $config['urls']['members'];

        foreach ($config['providers'] as $name => $provider) {
            $this->providers[$name] = new Provider($name, $provider);
        }
    }

    /**
     * @param $addOn
     *
     * @return array
     */
    public function getAddOn($addOn)
    {
        return $this->addOns[$addOn];
    }

    /**
     * @return array
     */
    public function getAddOns()
    {
        return $this->addOns;
    }

    /**
     * @param array $addOns
     *
     * @return Config
     */
    public function setAddOns(array $addOns)
    {
        $this->addOns = $addOns;

        return $this;
    }

    /**
     * @param $label
     *
     * @return array
     */
    public function getLabel($label)
    {
        return $this->labels[$label];
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param array $labels
     *
     * @return Config
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param $provider
     *
     * @return Provider
     */
    public function getProvider($provider)
    {
        return $this->providers[$provider];
    }

    /**
     * @return Provider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param Provider[] $providers
     *
     * @return Config
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;

        return $this;
    }

    /**
     * @param $parent
     * @param $key
     *
     * @return array
     */
    public function getTemplates($parent, $key)
    {
        if (!isset($this->templates[$parent][$key])) {
            throw new \BadMethodCallException(sprintf('Template of type "%s" and name of "%s" does not exist in configuration!', $parent, $key));
        }

        return $this->templates[$parent][$key];
    }

    /**
     * @param array $templates
     *
     * @return Config
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;

        return $this;
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
     * @return array
     */
    public function getRolesRegister()
    {
        return $this->rolesRegister;
    }

    /**
     * @param array $rolesRegister
     *
     * @return Config
     */
    public function setRolesRegister(array $rolesRegister)
    {
        $this->rolesRegister = $rolesRegister;

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
            'addons'       => [
                'zocial' => false,
            ],
            'labels'     => [
                'login'  => 'Login',
                'logout' => 'Logout',
            ],
            'registration' => true,
            'roles'        => [
                'admin'  => [
                    'root',
                ],
                'member' => [
                    'admin' => 'Administrator',
                ],
                'register' => [
                    'participant',
                ],
            ],
            'templates' => [
                'profile'        => [
                    'parent'   => 'members.twig',
                    'register' => 'members_register.twig',
                    'edit'     => 'members_profile_edit.twig',
                    'view'     => 'members_profile_view.twig',
                ],
                'authentication' => [
                    'parent'   => 'password.twig',
                    'login'    => '_login.twig',
                    'password' => '_password.twig',
                    'feedback' => '_feedback.twig',
                    'button'   => '_button.twig',
                ],
                'error'          => [
                    'parent' => 'members_error.twig',
                    'error'  => '_members_error.twig',
                ],
            ],
            'urls'         => [
                'authenticate' => 'authentication',
                'members'      => 'membership',
            ],
        ];
    }
}
