<?php

namespace Bolt\Extension\Bolt\Members\Config;

use Bolt\Helpers\Arr;

/**
 * Base configuration class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Config
{
    /** @var array */
    protected $addOns;
    /** @var array */
    protected $labels;
    /** @var array */
    protected $placeholders;
    /** @var Provider[] */
    protected $providers;
    /** @var  array */
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
        $defaultConfig = $this->getDefaultConfig();
        $config = Arr::mergeRecursiveDistinct($defaultConfig, $extensionConfig);

        $this->addOns =  $config['addons'];
        $this->debug = (boolean) $config['debug'];
        $this->labels =  $config['labels'];
        $this->placeholders =  $config['placeholders'];
        $this->registration = $config['registration'];
        $this->rolesAdmin = $config['roles']['admin'];
        $this->rolesMember = $config['roles']['member'];
        $this->rolesRegister = $config['roles']['register'];
        $this->templates = $config['templates'];
        $this->urlAuthenticate = $config['urls']['authenticate'];
        $this->urlMembers = $config['urls']['members'];

        foreach ($config['providers'] as $name => $provider) {
            $this->providers[strtolower($name)] = new Provider($name, $provider);
        }
        $this->providers['generic'] = new Provider('Generic', []);
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
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     *
     * @return Config
     */
    public function setDebug($debug)
    {
        $this->debug = (boolean) $debug;

        return $this;
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
     * @param $placeholder
     *
     * @return array
     */
    public function getPlaceholder($placeholder)
    {
        return $this->placeholders[$placeholder];
    }

    /**
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * @param array $placeholders
     *
     * @return Config
     */
    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;

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
     * @return Provider[]
     */
    public function getEnabledProviders()
    {
        $enabled = [];
        /** @var Provider $provider */
        foreach ($this->providers as $provider) {
            if ($provider->isEnabled()) {
                $name = strtolower($provider->getName());
                $enabled[$name] = $provider;
            }
        }

        return $enabled;
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
        return $this->registration['enabled'];
    }

    /**
     * @return array
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param array $registration
     *
     * @return Config
     */
    public function setRegistration(array $registration)
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
            'debug'        => false,
            'labels'       => [
                'login'           => 'Login',
                'logout'          => 'Logout',
                'displayname'     => 'Public Name',
                'email'           => 'Email Address',
                'password_first'  => 'Password',
                'password_second' => 'Repeat Password',
                'profile_save'    => 'Save & Continue',
            ],
            'placeholders' => [
                'displayname'     => 'The name you would like to display publicly…',
                'email'           => 'Your email address…',
                'password_first'  => 'Enter your password…',
                'password_second' => 'Repeat the above password…',
            ],
            'registration' => [
                'enabled' => true,
                'verify_name'  => null,
                'verify_email' => null,
            ],
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
                    'edit'     => '_edit.twig',
                    'register' => '_register.twig',
                    'view'     => '_view.twig',
                ],
                'authentication' => [
                    'parent'    => 'login.twig',
                    'feedback'  => 'feedback.twig',
                    'associate' => '_associate.twig',
                    'login'     => '_login.twig',
                    'logout'    => '_logout.twig',
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
