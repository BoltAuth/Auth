<?php

namespace Bolt\Extension\BoltAuth\Auth\Config;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * Base configuration class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Config
{
    /** @var Provider[] */
    protected $providers;
    /** @var  array */
    protected $registration;
    /** @var array */
    protected $rolesAdmin;
    /** @var array */
    protected $rolesAuth;
    /** @var array */
    protected $rolesRegister;
    /** @var string */
    protected $urlAuthenticate;
    /** @var string */
    protected $urlAuth;
    /** @var string */
    protected $notificationName;
    /** @var string */
    protected $notificationEmail;
    /** @var string */
    protected $notificationEmailFormat;
    /** @var string */
    protected $redirectLogin;
    /** @var string */
    protected $redirectLogout;
    /** @var  Forms */
    protected $forms;
    /** @var array */
    protected $firewalls;

    /**
     * Constructor.
     *
     * @param $extensionConfig
     */
    public function __construct(array $extensionConfig)
    {
        $defaultConfig = $this->getDefaultConfig();
        $config = array_replace_recursive($defaultConfig, $extensionConfig);

        $this->debug = (boolean) $config['debug'];
        $this->registration = $config['registration'];
        $this->rolesAdmin = $config['roles']['admin'];
        $this->rolesAuth = $config['roles']['auth'];
        $this->rolesRegister = $config['roles']['register'];
        $this->urlAuthenticate = $config['urls']['authenticate'];
        $this->urlAuth = $config['urls']['auth'];
        $this->notificationName = $config['notification']['name'];
        $this->notificationEmail = $config['notification']['email'];
        $this->notificationEmailFormat = $config['notification']['format'];
        $this->redirectLogin = $config['redirects']['login'];
        $this->redirectLogout = $config['redirects']['logout'];

        $this->forms = new Forms($config['forms']);

        foreach ($config['providers'] as $name => $provider) {
            $this->providers[strtolower($name)] = new Provider($name, $provider);
        }
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
     * @param string $provider
     *
     * @return bool
     */
    public function hasProvider($provider)
    {
        if (isset($this->providers[$provider])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $provider
     *
     * @return Provider
     */
    public function getProvider($provider)
    {
        if (!isset($this->providers[$provider])) {
            throw new \BadMethodCallException(sprintf('Provider "%s" does not exist in configuration!', $provider));
        }

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
     * @param string $type
     * @param string $key
     *
     * @return array
     */
    public function getTemplate($type, $key)
    {
        if ($key === 'default') {
            return  sprintf('@Auth/%s/_sub/%s.twig', $type, $type);
        }

        if (!$this->forms->get('templates')->has($type) || !$this->forms->get('templates')->get($type)->has($key)) {
            throw new \BadMethodCallException(sprintf('Template of type "%s" and name of "%s" does not exist in configuration!', $type, $key));
        }

        return $this->forms->get('templates')->get($type)->get($key);
    }

    /**
     * @param string $type
     * @param string $key
     * @param string $value
     *
     * @return Config
     */
    public function setTemplate($type, $key, $value)
    {
        $this->forms->get('templates')->get($type)->set($key, $value);

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
     * @return boolean
     */
    public function isRegistrationAutomatic()
    {
        return $this->registration['auto'];
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
    public function getRolesAuth()
    {
        return (array) $this->rolesAuth;
    }

    /**
     * @param array $rolesAuth
     *
     * @return Config
     */
    public function setRolesAuth(array $rolesAuth)
    {
        $this->rolesAuth = $rolesAuth;

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
    public function getUrlAuth()
    {
        return $this->urlAuth;
    }

    /**
     * @param string $urlAuth
     *
     * @return Config
     */
    public function setUrlAuth($urlAuth)
    {
        $this->urlAuth = $urlAuth;

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
     * @return string
     */
    public function getNotificationEmailFormat()
    {
        return $this->notificationEmailFormat;
    }

    /**
     * @param string $notificationEmailFormat
     *
     * @return Config
     */
    public function setNotificationEmailFormat($notificationEmailFormat)
    {
        $this->notificationEmailFormat = $notificationEmailFormat;

        return $this;
    }

    /**
     * @return array
     */
    public function getFirewalls()
    {
        if ($this->firewalls === null) {
            return $this->firewalls;
        }

        $firewalls = [];
        foreach ($this->firewalls as $firewall) {
            $pattern = $firewall['pattern'];
            if (is_string($pattern)) {
                $pattern = ['path' => $pattern];
            }
            $firewalls[] = $this->getFirewallRequestMatcher($pattern);
        }

        return $this->firewalls = $firewalls;
    }

    /**
     * @param array $firewalls
     *
     * @return Config
     */
    public function setFirewalls(array $firewalls)
    {
        $this->firewalls = $firewalls;

        return $this;
    }

    /**
     * @param $addOn
     *
     * @return array
     */
    public function getAddOn($addOn)
    {
        return $this->forms->getAddOns()->get($addOn);
    }

    /**
     * @return ParameterBag
     */
    public function getAddOns()
    {
        return $this->forms->getAddOns();
    }

    /**
     * @param ParameterBag $addOns
     *
     * @return Config
     */
    public function setAddOns(ParameterBag $addOns)
    {
        $this->forms->set('addons', $addOns);

        return $this;
    }

    /**
     * @param $label
     *
     * @return array
     */
    public function getLabel($label)
    {
        return $this->forms->getLabels()->get($label);
    }

    /**
     * @return ParameterBag
     */
    public function getLabels()
    {
        return $this->forms->getLabels();
    }

    /**
     * @param ParameterBag $labels
     *
     * @return Config
     */
    public function setLabels(ParameterBag $labels)
    {
        $this->forms->set('labels', $labels);

        return $this;
    }

    /**
     * @param $placeholder
     *
     * @return array
     */
    public function getPlaceholder($placeholder)
    {
        return $this->forms->get('placeholders')->get($placeholder);
    }

    /**
     * @return ParameterBag
     */
    public function getPlaceholders()
    {
        return $this->forms->get('placeholders');
    }

    /**
     * @param ParameterBag $placeholders
     *
     * @return Config
     */
    public function setPlaceholders(ParameterBag $placeholders)
    {
        $this->forms->set('placeholders', $placeholders);

        return $this;
    }

    /**
     * @param array $pattern
     *
     * @return RequestMatcher
     */
    private function getFirewallRequestMatcher(array $pattern)
    {
        $pattern += [
            'path'       => null,
            'host'       => null,
            'methods'    => null,
            'ips'        => null,
            'attributes' => [],
            'schemes'    => null,
        ];

        return new RequestMatcher($pattern['path'], $pattern['host'], $pattern['methods'], $pattern['ips'], $pattern['attributes'], $pattern['schemes']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'debug'        => false,
            'registration' => [
                'enabled'      => true,
                'auto'         => false,
            ],
            'notification' => [
                'name'   => null,
                'email'  => null,
                'format' => 'mixed',
            ],
            'redirects'    => [
                'login'  => null,
                'logout' => null,
            ],
            'roles'        => [
                'admin'  => [
                    'root',
                ],
                'auth' => [
                    'admin'       => 'Administrator',
                    'participant' => 'Participant',
                ],
                'register' => [
                    'participant',
                ],
            ],
            'urls'         => [
                'authenticate' => 'authentication',
                'auth'      => 'auth',
            ],
            'forms'         => [
                'templates' => [
                    'profile'        => [
                        'parent'   => '@Auth/profile/_profile.twig',
                        'edit'     => '@Auth/profile/edit.twig',
                        'register' => '@Auth/profile/register.twig',
                        'verify'   => '@Auth/profile/verify.twig',
                        'view'     => '@Auth/profile/view.twig',
                    ],
                    'authentication' => [
                        'parent'    => '@Auth/authentication/_authentication.twig',
                        'associate' => '@Auth/authentication/associate.twig',
                        'login'     => '@Auth/authentication/login.twig',
                        'logout'    => '@Auth/authentication/logout.twig',
                        'recovery'  => '@Auth/authentication/recovery.twig',
                    ],
                    'error'          => [
                        'parent' => '@Auth/error/_error.twig',
                        'error'  => '@Auth/error/error.twig',
                    ],
                    'feedback'          => [
                        'feedback'  => '@Auth/feedback/feedback.twig',
                    ],
                    'recovery'   => [
                        'subject' => '@Auth/authentication/recovery/subject.twig',
                        'html'    => '@Auth/authentication/recovery/html.twig',
                        'text'    => '@Auth/authentication/recovery/text.twig',
                    ],
                    'verification'   => [
                        'subject' => '@Auth/profile/registration/subject.twig',
                        'html'    => '@Auth/profile/registration/html.twig',
                        'text'    => '@Auth/profile/registration/text.twig',
                    ],
                ],
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
                'addons'       => [
                    'zocial' => false,
                ],
            ],
            'firewalls'    => null,
        ];
    }
}
