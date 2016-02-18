<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Bolt\Configuration\ResourceManager;
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
    /** @var AccessControl\Session */
    private $session;
    /** @var Storage\Records */
    private $records;
    /** @var Form\Manager */
    private $formManager;
    /** @var ResourceManager */
    private $resourceManager;

    /**
     * Constructor.
     *
     * @param Config                $config
     * @param AccessControl\Session $session
     * @param Storage\Records       $records
     * @param Form\Manager          $formManager
     * @param ResourceManager       $resourceManager
     */
    public function __construct(
        Config $config,
        AccessControl\Session $session,
        Storage\Records $records,
        Form\Manager $formManager,
        ResourceManager $resourceManager
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->records = $records;
        $this->formManager = $formManager;
        $this->resourceManager = $resourceManager;
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
            new \Twig_SimpleFunction('members_auth_password', [$this, 'renderPassword'], $safe + $env),
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
     * @param array           $exclude  An array of provider names to not include
     *
     * @return TwigMarkup
     */
    public function renderLogin(TwigEnvironment $twig, $redirect = false, $exclude = [])
    {
        // Set redirect if requested
        $target = $redirect ? ['redirect' => $this->resourceManager->getUrl('current')] : [];
        $context = ['providers' => null];

        foreach ($this->config->getProviders() as $provider => $providerConf) {
            if (!$providerConf->isEnabled() || in_array($providerConf->getName(), $exclude)) {
                continue;
            }

            $link = sprintf('%s%s/login/process?%s',
                $this->resourceManager->getUrl('root'),
                $this->config->getUrlAuthenticate(),
                http_build_query(['provider' => $provider] + $target)
            );

            $context['providers'][$provider] = [
                'link'  => $link,
                'label' => $providerConf->getLabel() ?: $provider,
                'class' => $this->getCssClass(strtolower($provider)),
            ];
        }

        $html = $twig->render($this->config->getTemplates('authentication', 'button'), $context);

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
        $target = $redirect ? ['redirect' => $this->resourceManager->getUrl('current')] : [];
        $link = sprintf('%s%s/logout?',
            $this->resourceManager->getUrl('root'),
            $this->config->getUrlAuthenticate(),
            http_build_query($target)
        );
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
     * Display the login prompt.
     *
     * @param TwigEnvironment $twig
     *
     * @return TwigMarkup
     */
    public function renderPassword(TwigEnvironment $twig)
    {
        $request = new Request();
        $form = $this->formManager->getFormLogin($twig, $request);
        $html = $form->getRenderedForm($this->config->getTemplates('authentication', 'login'));

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
