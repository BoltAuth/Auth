<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment as TwigEnvironment;
use Twig_Markup as TwigMarkup;

/**
 * Twig functions.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Functions extends \Twig_Extension
{
    /** @var Config */
    private $config;
    /** @var Form\Manager */
    private $formManager;
    /** @var Storage\Records */
    private $records;
    /** @var AccessControl\Session */
    private $session;

    /**
     * Constructor.
     *
     * @param Config                $config
     * @param Form\Manager          $formManager
     * @param Storage\Records       $records
     * @param AccessControl\Session $session
     */
    public function __construct(
        Config $config,
        Form\Manager $formManager,
        Storage\Records $records,
        AccessControl\Session $session
    ) {
        $this->config = $config;
        $this->formManager = $formManager;
        $this->records = $records;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Members';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $safe = ['is_safe' => ['html'], 'is_safe_callback' => true];
        $env  = ['needs_environment' => true];

        return [
            new \Twig_SimpleFunction('is_member', [$this, 'isMember'],       $safe),
            new \Twig_SimpleFunction('member_has_role', [$this, 'hasRole'],        $safe),
            new \Twig_SimpleFunction('member_providers', [$this, 'getProviders'],   $safe),
            new \Twig_SimpleFunction('members_auth_switcher', [$this, 'renderSwitcher'], $safe + $env),
            new \Twig_SimpleFunction('members_auth_login', [$this, 'renderLogin'],    $safe + $env),
            new \Twig_SimpleFunction('members_auth_logout', [$this, 'renderLogout'],   $safe + $env),
        ];
    }

    /**
     * Check if the current session is a logged-in member.
     *
     * @return bool
     */
    public function isMember()
    {
        return $this->session->hasAuthorisation();
    }

    /**
     * Check if the current logged-in session has a member role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        $auth = $this->session->getAuthorisation();
        if ($auth === null) {
            return false;
        }
        $account = $this->records->getAccountByGuid($auth->getGuid());
        if ($account === false) {
            return false;
        }
        $roles = (array) $account->getRoles();

        return in_array($role, $roles);
    }

    /**
     * Return an array of registered OAuth providers for an account.
     *
     * @return array
     */
    public function getProviders()
    {
        $providers = [];
        $auth = $this->session->getAuthorisation();
        if ($auth === null) {
            return $providers;
        }

        $providerEntities = $this->records->getProvisionsByGuid($auth->getGuid());
        if ($providerEntities === null) {
            return $providers;
        }

        /** @var Storage\Entity\Provider $providerEntity */
        foreach ($providerEntities as $providerEntity) {
            $providers[] = $providerEntity->getProvider();
        }

        return $providers;
    }

    /**
     * Display login/logout button(s) depending on status.
     *
     * @param TwigEnvironment $twig
     * @param bool            $redirect
     *
     * @return TwigMarkup
     */
    public function renderSwitcher(TwigEnvironment $twig, $redirect = false)
    {
        if ($this->session->getAuthorisation()) {
            return $this->renderLogout($twig, $redirect);
        }

        return $this->renderLogin($twig, $redirect);
    }

    /**
     * Display logout button(s).
     *
     * @param TwigEnvironment $twig
     * @param bool            $redirect If we should redirect after login
     *
     * @return TwigMarkup
     */
    public function renderLogin(TwigEnvironment $twig, $redirect = false)
    {
        $request = new Request();
        $form = $this->formManager->getFormLogin($twig, $request);
        $html = $form->getRenderedForm($this->config->getTemplates('authentication', 'login'));

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display logout button.
     *
     * @param TwigEnvironment $twig
     * @param bool            $redirect
     *
     * @return TwigMarkup
     */
    public function renderLogout(TwigEnvironment $twig, $redirect = false)
    {
        // Set redirect if requested
        $link = sprintf('%s/logout', $this->config->getUrlAuthenticate());
        $context = [
            'providers' => [
                'logout' => [
                    'link'  => $link,
                    'label' => $this->config->getLabel('logout'),
                    'class' => 'logout',
                ],
            ],
        ];

        $html = $twig->render($this->config->getTemplates('authentication', 'button'), $context);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Get a button's CSS class
     *
     * @param string $provider
     *
     * @return string
     */
    private function getCssClass($provider)
    {
        return $this->config->getAddOn('zocial') ? "zocial $provider" : $provider;
    }
}
