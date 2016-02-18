<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Storage;
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
    /** @var Login */
    protected $formLogin;
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
     * @param Login                 $formLogin
     * @param Profile               $formProfile
     * @param Register              $formRegister
     */
    public function __construct(
        Config $config,
        AccessControl\Session $session,
        Feedback $feedback,
        Storage\Records $records,
        Login $formLogin,
        Profile $formProfile,
        Register $formRegister
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->feedback = $feedback;
        $this->records = $records;
        $this->formLogin = $formLogin;
        $this->formProfile = $formProfile;
        $this->formRegister = $formRegister;
    }

    /**
     * Return the resolved login form.
     *
     * @param TwigEnvironment $twig
     * @param Request         $request
     *
     * @return ResolvedForm
     */
    public function getFormLogin(TwigEnvironment $twig, Request $request)
    {
        $form = $this->formLogin
            ->setRequest($request)
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $resolved = new ResolvedForm($form, $twig);
        $resolved->setContext([
            'twigparent' => $this->config->getTemplates('authentication', 'parent'),
            'auth_form'  => $form->createView(),
            'feedback'   => $this->feedback,
            'has_local'  => $this->config->getProvider('Local')->isEnabled(),
        ]);

        return $resolved;
    }

    /**
     * Return the resolved profile editing form.
     *
     * @param TwigEnvironment $twig
     * @param Request         $request
     *
     * @return ResolvedForm
     */
    public function getFormProfile(TwigEnvironment $twig, Request $request)
    {
        $form = $this->formProfile
            ->setGuid($this->session->getAuthorisation()->getGuid())
            ->setAction(sprintf('/%s/register', $this->config->getUrlMembers()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $resolved = new ResolvedForm($form, $twig);
        $resolved->setContext([
            'twigparent'   => $this->config->getTemplates('profile', 'parent'),
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
     * @param array           $defaultRoles
     *
     * @return ResolvedForm
     */
    public function getFormRegister(TwigEnvironment $twig, Request $request, array $defaultRoles)
    {
        $form = $this->formRegister
            ->setClientIp($request->getClientIp())
            ->setRoles($defaultRoles)
            ->setSession($this->session)
            ->setAction(sprintf('/%s/register', $this->config->getUrlMembers()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $resolved = new ResolvedForm($form, $twig);
        $resolved->setContext([
            'twigparent'   => $this->config->getTemplates('profile', 'parent'),
            'profile_form' => $form->createView(),
            'feedback'     => $this->feedback,
        ]);

        return $resolved;
    }
}
