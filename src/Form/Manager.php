<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Storage;
use Pimple as Container;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment as TwigEnvironment;

/**
 * Form Manager.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Manager
{
    /** @var Config */
    protected $config;
    /** @var AccessControl\Session */
    protected $session;
    /** @var Feedback */
    protected $feedback;
    /** @var Storage\Records  */
    protected $records;
    /** @var Container */
    protected $forms;
    /** @var Associate */
    protected $formAssociate;
    /** @var Login */
    protected $formLogin;
    /** @var Logout */
    protected $formLogout;
    /** @var Oauth */
    protected $formOauth;
    /** @var Profile */
    protected $formProfile;
    /** @var Register */
    protected $formRegister;

    /**
     * Constructor.
     *
     * @param Config                $config
     * @param AccessControl\Session $session
     * @param Feedback              $feedback
     * @param Storage\Records       $records
     * @param Container             $forms
     */
    public function __construct(
        Config $config,
        AccessControl\Session $session,
        Feedback $feedback,
        Storage\Records $records,
        Container $forms
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->feedback = $feedback;
        $this->records = $records;
        $this->forms = $forms;
    }

    /**
     * Return the resolved association form.
     *
     * @param TwigEnvironment $twig
     * @param Request         $request
     * @param bool            $includeParent
     *
     * @return ResolvedForm
     */
    public function getFormAssociate(TwigEnvironment $twig, Request $request, $includeParent = true)
    {
        $form = $this->getFormServiceAssociate()
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;

        $resolved = new ResolvedForm($form, $twig);
        $resolved->setContext([
            'twigparent'   => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '_sub/login.twig',
            'password_form'    => $form->createView(),
            'feedback'     => $this->feedback,
            'providers'    => $this->config->getEnabledProviders(),
        ]);

        return $resolved;
    }

    /**
     * Return the resolved login form.
     *
     * @param TwigEnvironment $twig
     * @param Request         $request
     * @param bool            $includeParent
     *
     * @return ResolvedForm
     */
    public function getFormLogin(TwigEnvironment $twig, Request $request, $includeParent = true)
    {
        $formLogin = $this->getFormServiceLoginPassword()
            ->setRequest($request)
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $formOauth = $this->getFormServiceLoginOauth()
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $formRegister = $this->getFormServiceRegister()
            ->setClientIp($request->getClientIp())
            ->setRoles($this->config->getRolesRegister())
            ->setSession($this->session)
            ->setAction(sprintf('/%s/profile/register', $this->config->getUrlMembers()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $resolved = new ResolvedForm($formLogin, $twig);
        $resolved->setContext([
            'twigparent'    => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '_sub/login.twig',
            'password_form' => $formLogin->createView(),
            'oauth_form'    => $formOauth->createView(),
            'profile_form'  => $formRegister->createView(),
            'feedback'      => $this->feedback,
            'providers'     => $this->config->getEnabledProviders(),
        ]);

        return $resolved;
    }

    /**
     * Return the resolved logout form.
     *
     * @param TwigEnvironment $twig
     * @param Request         $request
     * @param bool            $includeParent
     *
     * @return ResolvedForm
     */
    public function getFormLogout(TwigEnvironment $twig, Request $request, $includeParent = true)
    {
        $form = $this->getFormServiceLogout()
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $resolved = new ResolvedForm($form, $twig);
        $resolved->setContext([
            'twigparent'    => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '_sub/logout.twig',
            'password_form' => $form->createView(),
            'feedback'      => $this->feedback,
            'providers'     => $this->config->getEnabledProviders(),
        ]);

        return $resolved;
    }

    /**
     * Return the resolved profile editing form.
     *
     * @param TwigEnvironment $twig
     * @param Request         $request
     * @param bool            $includeParent
     * @param string          $guid
     *
     * @return ResolvedForm
     */
    public function getFormProfile(TwigEnvironment $twig, Request $request, $includeParent = true, $guid = null)
    {
        if ($guid === null) {
            $guid = $this->session->getAuthorisation()->getGuid();
        }
        $form = $this->getFormServiceProfile()
            ->setGuid($guid)
            ->setAction(sprintf('/%s/profile/register', $this->config->getUrlMembers()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $resolved = new ResolvedForm($form, $twig);
        $resolved->setContext([
            'twigparent'   => $includeParent ? $this->config->getTemplates('profile', 'parent') : '_sub/members.twig',
            'profile_form' => $form->createView(),
            'feedback'     => $this->feedback,
        ]);

        return $resolved;
    }

    /**
     * Return the resolved registration form.
     *
     * @param TwigEnvironment $twig
     * @param Request         $request
     * @param bool            $includeParent
     *
     * @return ResolvedForm
     */
    public function getFormRegister(TwigEnvironment $twig, Request $request, $includeParent = true)
    {
        $formLogin = $this->getFormServiceLoginPassword()
            ->setRequest($request)
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $formRegister = $this->getFormServiceRegister()
            ->setClientIp($request->getClientIp())
            ->setRoles($this->config->getRolesRegister())
            ->setSession($this->session)
            ->setAction(sprintf('/%s/profile/register', $this->config->getUrlMembers()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $resolved = new ResolvedForm($formRegister, $twig);
        $resolved->setContext([
            'twigparent'   => $includeParent ? $this->config->getTemplates('profile', 'parent') : '_sub/members.twig',
            'password_form'    => $formLogin->createView(),
            'profile_form' => $formRegister->createView(),
            'feedback'     => $this->feedback,
        ]);

        return $resolved;
    }

    /**
     * Return the association form service provider.
     *
     * @return Associate
     */
    protected function getFormServiceAssociate()
    {
        if ($this->formAssociate === null) {
            $this->formAssociate = $this->forms['associate'];
        }

        return $this->formAssociate;
    }

    /**
     * Return the profile form service provider.
     *
     * @return LoginOauth
     */
    protected function getFormServiceLoginOauth()
    {
        if ($this->formOauth === null) {
            $this->formOauth = $this->forms['login_oauth'];
        }

        return $this->formOauth;
    }

    /**
     * Return the login form service provider.
     *
     * @return LoginPassword
     */
    protected function getFormServiceLoginPassword()
    {
        if ($this->formLogin === null) {
            $this->formLogin = $this->forms['login_password'];
        }

        return $this->formLogin;
    }

    /**
     * Return the logout form service provider.
     *
     * @return Logout
     */
    protected function getFormServiceLogout()
    {
        if ($this->formLogout === null) {
            $this->formLogout = $this->forms['logout'];
        }

        return $this->formLogout;
    }

    /**
     * Return the profile form service provider.
     *
     * @return Profile
     */
    protected function getFormServiceProfile()
    {
        if ($this->formProfile === null) {
            $this->formProfile = $this->forms['profile'];
        }

        return $this->formProfile;
    }

    /**
     * Return the register form service provider.
     *
     * @return Register
     */
    protected function getFormServiceRegister()
    {
        if ($this->formRegister === null) {
            $this->formRegister = $this->forms['register'];
        }

        return $this->formRegister;
    }
}
