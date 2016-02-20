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
            new \Twig_SimpleFunction('is_member',                [$this, 'isMember'],        $safe),
            new \Twig_SimpleFunction('member',                   [$this, 'getMember'],       $safe),
            new \Twig_SimpleFunction('member_meta',              [$this, 'getMemberMeta'],   $safe),
            new \Twig_SimpleFunction('member_has_role',          [$this, 'hasRole'],         $safe),
            new \Twig_SimpleFunction('member_providers',         [$this, 'getProviders'],    $safe),
            new \Twig_SimpleFunction('members_auth_switcher',    [$this, 'renderSwitcher'],  $safe + $env),
            new \Twig_SimpleFunction('members_auth_associate',   [$this, 'renderAssociate'], $safe + $env),
            new \Twig_SimpleFunction('members_auth_login',       [$this, 'renderLogin'],     $safe + $env),
            new \Twig_SimpleFunction('members_auth_logout',      [$this, 'renderLogout'],    $safe + $env),
            new \Twig_SimpleFunction('members_profile_edit',     [$this, 'renderEdit'],      $safe + $env),
            new \Twig_SimpleFunction('members_profile_register', [$this, 'renderRegister'],  $safe + $env),
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
     * @param TwigEnvironment $twig
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderSwitcher(TwigEnvironment $twig, $template = null)
    {
        if ($this->session->getAuthorisation()) {
            return $this->renderLogout($twig, $template);
        }

        return $this->renderLogin($twig, $template);
    }

    /**
     * Display social login buttons to associate with an existing account.
     *
     * @param TwigEnvironment $twig
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderAssociate(TwigEnvironment $twig, $template = null)
    {
        if (!$this->session->hasAuthorisation()) {
            return $this->renderLogin($twig, $template);
        }

        $form = $this->formManager->getFormAssociate($twig, new Request(), false);
        $template = $template ?: $this->config->getTemplates('authentication', 'associate');
        $html = $form->getRenderedForm($template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display logout button(s).
     *
     * @param TwigEnvironment $twig
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderLogin(TwigEnvironment $twig, $template = null)
    {
        $form = $this->formManager->getFormLogin($twig, new Request(), false);
        $template = $template ?: $this->config->getTemplates('authentication', 'login');
        $html = $form->getRenderedForm($template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display logout button.
     *
     * @param TwigEnvironment $twig
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderLogout(TwigEnvironment $twig, $template = null)
    {
        $form = $this->formManager->getFormLogout($twig, new Request(), false);
        $template = $template ?: $this->config->getTemplates('authentication', 'logout');
        $html = $form->getRenderedForm($template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display that profile editing form.
     *
     * @param TwigEnvironment $twig
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderEdit(TwigEnvironment $twig, $template = null)
    {
        if (!$this->session->hasAuthorisation()) {
            return $this->renderLogin($twig, $template);
        }

        $form = $this->formManager->getFormProfile($twig, new Request(), false);
        $template = $template ?: $this->config->getTemplates('profile', 'edit');
        $html = $form->getRenderedForm($template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display the registration form.
     *
     * @param TwigEnvironment $twig
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderRegister(TwigEnvironment $twig, $template = null)
    {
        $form = $this->formManager->getFormRegister($twig, new Request(), false);
        $template = $template ?: $this->config->getTemplates('profile', 'register');
        $html = $form->getRenderedForm($template);

        return new TwigMarkup($html, 'UTF-8');
    }
}
