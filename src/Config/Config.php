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
    /** @var string */
    protected $notificationName;
    /** @var string */
    protected $notificationEmail;
    /** @var string */
    protected $redirectLogin;
    /** @var string */
    protected $redirectLogout;

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
        $this->notificationName = $config['notification']['name'];
        $this->notificationEmail = $config['notification']['email'];
        $this->redirectLogin = $config['redirects']['login'];
        $this->redirectLogout = $config['redirects']['logout'];

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
     * @return string
     */
    public function getRedirectLogin()
    {
        return $this->redirectLogin;
    }

    /**
     * @param string $redirectLogin
     *
     * @return Config
     */
    public function setRedirectLogin($redirectLogin)
    {
        $this->redirectLogin = $redirectLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectLogout()
    {
        return $this->redirectLogout;
    }

    /**
     * @param string $redirectLogout
     *
     * @return Config
     */
    public function setRedirectLogout($redirectLogout)
    {
        $this->redirectLogout = $redirectLogout;

        return $this;
    }

    /**
     * @param string $parent
     * @param string $key
     *
     * @return array
     */
    public function getTemplate($parent, $key)
    {
        if (!isset($this->templates[$parent][$key])) {
            throw new \BadMethodCallException(sprintf('Template of type "%s" and name of "%s" does not exist in configuration!', $parent, $key));
        }
        if ($key === 'default') {
            return  sprintf('@Members/%s/_sub/%s.twig', $key, $key);
        }

        return $this->templates[$parent][$key];
    }

    /**
     * @param string $parent
     * @param string $key
     *
     * @return Config
     */
    public function setTemplate($parent, $key)
    {
        $this->templates[$parent] = $key;

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
     * @return string
     */
    public function getNotificationName()
    {
        return $this->notificationName;
    }

    /**
     * @param string $notificationName
     *
     * @return Config
     */
    public function setNotificationName($notificationName)
    {
        $this->notificationName = $notificationName;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotificationEmail()
    {
        return $this->notificationEmail;
    }

    /**
     * @param string $notificationEmail
     *
     * @return Config
     */
    public function setNotificationEmail($notificationEmail)
    {
        $this->notificationEmail = $notificationEmail;

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
                'enabled'      => true,
            ],
            'notification' => [
                'name'  => null,
                'email' => null,
            ],
            'redirects'    => [
                'login'  => null,
                'logout' => null,
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
                    'parent'   => '@Members/profile/profile.twig',
                    'edit'     => '@Members/profile/_edit.twig',
                    'register' => '@Members/profile/_register.twig',
                    'verify'   => '@Members/profile/_verify.twig',
                    'view'     => '@Members/profile/_view.twig',
                ],
                'authentication' => [
                    'parent'    => '@Members/authentication/authentication.twig',
                    'associate' => '@Members/authentication/_associate.twig',
                    'login'     => '@Members/authentication/_login.twig',
                    'logout'    => '@Members/authentication/_logout.twig',
                    'recovery'  => '@Members/authentication/_recovery.twig',
                ],
                'error'          => [
                    'parent' => '@Members/error/members_error.twig',
                    'error'  => '@Members/error/_members_error.twig',
                ],
                'feedback'          => [
                    'feedback'  => '@Members/feedback/feedback.twig',
                ],
                'recovery'   => [
                    'subject' => '@Members/authentication/recovery/subject.twig',
                    'body'    => '@Members/authentication/recovery/body.twig',
                ],
                'verification'   => [
                    'subject' => '@Members/profile/registration/subject.twig',
                    'body'    => '@Members/profile/registration/body.twig',
                ],
            ],
            'urls'         => [
                'authenticate' => 'authentication',
                'members'      => 'membership',
            ],
        ];
    }
}
