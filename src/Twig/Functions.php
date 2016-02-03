<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Bolt\Configuration\ResourceManager;
use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Extension\Bolt\Members\Storage\Records;
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
class Functions
{
    /** @var Config */
    private $config;
    /** @var Session */
    private $session;
    /** @var Records */
    private $records;
    /** @var ResourceManager */
    private $resourceManager;

    /**
     * Constructor.
     *
     * @param Config          $config
     * @param Session         $session
     * @param Records         $records
     * @param ResourceManager $resourceManager
     */
    public function __construct(Config $config, Session $session, Records $records, ResourceManager $resourceManager)
    {
        $this->config = $config;
        $this->session = $session;
        $this->records = $records;
        $this->resourceManager = $resourceManager;
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
     * @return bool
     */
    public function hasRole()
    {
        return true;
    }

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

        /** @var Entity\Provider $providerEntity */
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
        $target = $redirect ? ['redirect' => $this->resourceManager->getUrl('current')]: [];
        $context = [];

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
        $target = $redirect ? ['redirect' => $this->resourceManager->getUrl('current')]: [];
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
