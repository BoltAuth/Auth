<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\HttpFoundation\Request;
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
            new \Twig_SimpleFunction('is_member',                [$this, 'isMember'],        $safe),
            new \Twig_SimpleFunction('member',                   [$this, 'getMember'],       $safe),
            new \Twig_SimpleFunction('member_meta',              [$this, 'getMemberMeta'],   $safe),
            new \Twig_SimpleFunction('member_has_role',          [$this, 'hasRole'],         $safe),
            new \Twig_SimpleFunction('member_providers',         [$this, 'getProviders'],    $safe),
            new \Twig_SimpleFunction('members_auth_switcher',    [$this, 'renderSwitcher'],  $safe),
            new \Twig_SimpleFunction('members_auth_associate',   [$this, 'renderAssociate'], $safe),
            new \Twig_SimpleFunction('members_auth_login',       [$this, 'renderLogin'],     $safe),
            new \Twig_SimpleFunction('members_auth_logout',      [$this, 'renderLogout'],    $safe),
            new \Twig_SimpleFunction('members_profile_edit',     [$this, 'renderEdit'],      $safe),
            new \Twig_SimpleFunction('members_profile_register', [$this, 'renderRegister'],  $safe),
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
     * Return a member's account.
     *
     * @return Storage\Entity\Account|null
     */
    public function getMember()
    {
        if (!$this->session->hasAuthorisation()) {
            return null;
        }
        $auth = $this->session->getAuthorisation();
        $account = $this->records->getAccountByGuid($auth->getGuid());

        return $account ?: null;
    }

    /**
     * Return a member's account meta data.
     *
     * @return Storage\Entity\AccountMeta[]|null
     */
    public function getMemberMeta()
    {
        if (!$this->session->hasAuthorisation()) {
            return null;
        }
        $auth = $this->session->getAuthorisation();
        $meta = $this->records->getAccountMetaAll($auth->getGuid());

        return $meta ?: null;
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
     * @param string $guid
     *
     * @return array
     */
    public function getProviders($guid = null)
    {
        $providers = [];
        if(empty($guid)) {
            $auth = $this->session->getAuthorisation();

            if ($auth === null) {
                return $providers;
            }

            $guid = $auth->getGuid();
        }

        $providerEntities = $this->records->getProvisionsByGuid($guid);
        if ($providerEntities === false) {
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
     * @param string $template
     *
     * @return TwigMarkup
     */
    public function renderSwitcher($template = null)
    {
        if ($this->session->getAuthorisation()) {
            return $this->renderLogout($template);
        }

        return $this->renderLogin($template);
    }

    /**
     * Display social login buttons to associate with an existing account.
     *
     * @param string $template
     *
     * @return TwigMarkup
     */
    public function renderAssociate($template = null)
    {
        if (!$this->session->hasAuthorisation()) {
            return $this->renderLogin($template);
        }

        $template = $template ?: $this->config->getTemplates('authentication', 'associate');
        $form = $this->formManager->getFormAssociate(new Request(), false);
        $html = $this->formManager->renderForms($form, $template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display logout button(s).
     *
     * @param string $template
     *
     * @return TwigMarkup
     */
    public function renderLogin($template = null)
    {
        $context = ['transitional' => $this->session->isTransitional()];
        $template = $template ?: $this->config->getTemplates('authentication', 'login');
        $form = $this->formManager->getFormLogin(new Request(), false);
        $html = $this->formManager->renderForms($form, $template, $context);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display logout button.
     *
     * @param string $template
     *
     * @return TwigMarkup
     */
    public function renderLogout($template = null)
    {
        $template = $template ?: $this->config->getTemplates('authentication', 'logout');
        $form = $this->formManager->getFormLogout(new Request(), false);
        $html = $this->formManager->renderForms($form, $template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display that profile editing form.
     *
     * @param string $template
     *
     * @return TwigMarkup
     */
    public function renderEdit($template = null)
    {
        if (!$this->session->hasAuthorisation()) {
            return $this->renderLogin($template);
        }

        $template = $template ?: $this->config->getTemplates('profile', 'edit');
        $form = $this->formManager->getFormProfileEdit(new Request(), false);
        $html = $this->formManager->renderForms($form, $template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display the registration form.
     *
     * @param string $template
     *
     * @return TwigMarkup
     */
    public function renderRegister($template = null)
    {
        $context = ['transitional' => $this->session->isTransitional()];
        $template = $template ?: $this->config->getTemplates('profile', 'register');
        $form = $this->formManager->getFormProfileRegister(new Request(), false);
        $html = $this->formManager->renderForms($form, $template, $context);

        return new TwigMarkup($html, 'UTF-8');
    }
}
