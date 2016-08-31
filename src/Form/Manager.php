<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Form\Builder;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment as TwigEnvironment;
use Twig_Markup as TwigMarkup;

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
    /** @var Generator */
    private $formGenerator;

    /**
     * Constructor.
     *
     * @param Config                $config
     * @param AccessControl\Session $session
     * @param Feedback              $feedback
     * @param Storage\Records       $records
     * @param Generator             $formGenerator
     */
    public function __construct(
        Config $config,
        AccessControl\Session $session,
        Feedback $feedback,
        Storage\Records $records,
        Generator $formGenerator
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->feedback = $feedback;
        $this->records = $records;
        $this->formGenerator = $formGenerator;
    }

    /**
     * Return the resolved association form.
     *
     * @param Request $request
     * @param bool    $includeParent
     *
     * @return ResolvedFormBuild
     */
    public function getFormAssociate(Request $request, $includeParent = true)
    {
        /** @var Builder\Associate $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('associate');
        $form = $baseForm
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;

        $extraContext = [
            'twigparent' => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '_sub/login.twig',
        ];

        return new ResolvedFormBuild([$form], $extraContext);
    }

    /**
     * Return the resolved login form.
     *
     * @param Request $request
     * @param bool    $includeParent
     *
     * @return ResolvedFormBuild
     */
    public function getFormLogin(Request $request, $includeParent = true)
    {
        $twigParent = $includeParent ? $this->config->getTemplates('authentication', 'parent') : '_sub/login.twig';

        return $this->getFormCombinedLogin($request, $twigParent);
    }

    /**
     * Return the resolved logout form.
     *
     * @param Request $request
     * @param bool    $includeParent
     *
     * @return ResolvedFormBuild
     */
    public function getFormLogout(Request $request, $includeParent = true)
    {
        /** @var Builder\Logout $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('logout');
        $form = $baseForm
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $extraContext = [
            'twigparent' => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '_sub/logout.twig',
        ];

        return new ResolvedFormBuild([$form], $extraContext);
    }

    /**
     * Return the resolved profile editing form.
     *
     * @param Request $request
     * @param bool    $includeParent
     * @param string  $guid
     *
     * @return ResolvedFormBuild
     */
    public function getFormProfileEdit(Request $request, $includeParent = true, $guid = null)
    {
        if ($guid === null) {
            $guid = $this->session->getAuthorisation()->getGuid();
        }
        /** @var Builder\Profile $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('profile_edit');
        $formEdit = $baseForm
            ->setGuid($guid)
            ->setAction(sprintf('/%s/profile/edit', $this->config->getUrlMembers()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        /** @var Builder\Associate $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('associate');
        $formAssociate = $baseForm
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;

        $extraContext = [
            'twigparent' => $includeParent ? $this->config->getTemplates('profile', 'parent') : '@Members/profile/_sub/profile.twig',
        ];

        return new ResolvedFormBuild([$formEdit, $formAssociate], $extraContext);
    }

    /**
     * Return the resolved profile account recovery form.
     *
     * @param Request $request
     * @param bool    $includeParent
     *
     * @return ResolvedFormBuild
     */
    public function getFormProfileRecovery(Request $request, $includeParent = true)
    {
        /** @var Builder\ProfileRecovery $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('profile_recovery');
        $form = $baseForm
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        $extraContext = [
            'twigparent' => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '@Members/profile/_sub/profile.twig',
        ];

        return new ResolvedFormBuild([$form], $extraContext);
    }

    /**
     * Return the resolved registration form.
     *
     * @param Request $request
     * @param bool    $includeParent
     *
     * @return ResolvedFormBuild
     */
    public function getFormProfileRegister(Request $request, $includeParent = true)
    {
        $twigParent = $includeParent ? $this->config->getTemplates('profile', 'parent') : '@Members/profile/_sub/profile.twig';

        return $this->getFormCombinedLogin($request, $twigParent);
    }

    /**
     * Render given forms in a template.
     *
     * @param ResolvedFormBuild $resolvedForm
     * @param TwigEnvironment   $twigEnvironment
     * @param string            $template
     * @param array             $context
     *
     * @return TwigMarkup
     */
    public function renderForms(ResolvedFormBuild $resolvedForm, TwigEnvironment $twigEnvironment, $template, array $context = [])
    {
        $context += $resolvedForm->getContext();
        /** @var FormInterface $form */
        foreach ($resolvedForm->getForms() as $form) {
            $formName = sprintf('form_%s', $form->getName());
            $context[$formName] = $form->createView();
        }
        $context['feedback'] = $this->feedback;
        $context['providers'] = $this->config->getEnabledProviders();
        $html = $twigEnvironment->render($template, $context);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Return the combined login & registration resolved form object.
     *
     * @param Request $request
     * @param string  $twigParent
     *
     * @return ResolvedFormBuild
     */
    protected function getFormCombinedLogin(Request $request, $twigParent)
    {
        /** @var Builder\Associate $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('associate');
        $associateForm = $baseForm
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        /** @var Builder\LoginOauth $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('login_oauth');
        $formOauth = $baseForm
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        /** @var Builder\LoginPassword $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('login_password');
        $formPassword = $baseForm
            ->setRequest($request)
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;
        /** @var Builder\ProfileRegister $baseForm */
        $baseForm = $this->formGenerator->getResolvedBuild('profile_register');
        $formRegister = $baseForm
            ->setClientIp($request->getClientIp())
            ->setRoles($this->config->getRolesRegister())
            ->setSession($this->session)
            ->setAction(sprintf('/%s/profile/register', $this->config->getUrlMembers()))
            ->createForm($this->records)
            ->handleRequest($request)
        ;

        return new ResolvedFormBuild([$associateForm, $formOauth, $formPassword, $formRegister], ['twigparent' => $twigParent]);
    }
}
