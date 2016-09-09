<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\AccessControl\Validator\AccountVerification;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
use Bolt\Extension\Bolt\Members\Storage\Entity\ProfileManager;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Frontend controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Frontend implements ControllerProviderInterface
{
    /** @var Config */
    private $config;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = $app['controllers_factory'];

        $ctr->match('/profile/edit', [$this, 'editProfile'])
            ->bind('membersProfileEdit')
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/profile/register', [$this, 'registerProfile'])
            ->bind('membersProfileRegister')
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/profile/verify', [$this, 'verifyProfile'])
            ->bind('membersProfileVerify')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/profile/view', [$this, 'viewProfile'])
            ->bind('membersProfileView')
            ->method(Request::METHOD_GET)
        ;

        // Own the rest of the base route
        $ctr->match('/', [$this, 'defaultRoute'])
            ->bind('membersDefaultBase')
        ;

        $ctr->match('/{url}', [$this, 'defaultRoute'])
            ->bind('membersDefault')
            ->assert('url', '.+')
        ;

        $ctr->after([$this, 'after']);

        return $ctr;
    }

    /**
     * Middleware to modify the Response before it is sent to the client.
     *
     * @param Request     $request
     * @param Response    $response
     * @param Application $app
     */
    public function after(Request $request, Response $response, Application $app)
    {
        /** @var Session $session */
        $session = $app['members.session'];
        if ($session->getAuthorisation() === null) {
            $response->headers->clearCookie(Session::COOKIE_AUTHORISATION);

            return;
        }

        $cookie = $session->getAuthorisation()->getCookie();
        if ($cookie === null) {
            $response->headers->clearCookie(Session::COOKIE_AUTHORISATION);
        } else {
            $response->headers->setCookie(new Cookie(Session::COOKIE_AUTHORISATION, $cookie, 86400));
        }

        $request->attributes->set('members-cookies', 'set');
    }

    /**
     * Default catch-all route.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return RedirectResponse
     */
    public function defaultRoute(Application $app, Request $request)
    {
        if ($app['members.session']->hasAuthorisation()) {
            return new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));
        }

        return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
    }

    /**
     * Edit an existing member profile.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function editProfile(Application $app, Request $request)
    {
        /** @var Session $session */
        $session = $app['members.session'];
        /** @var Form\Manager $formsManager */
        $formsManager = $app['members.forms.manager'];

        $memberSession = $session->getAuthorisation();
        if ($memberSession === null) {
            $app['session']->set(Authentication::FINAL_REDIRECT_KEY, $request->getUri());
            $app['members.feedback']->info('Login required to edit your profile');

            return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
        }

        // Handle the form request data
        $resolvedBuild = $formsManager->getFormProfileEdit($request, true, $session->getAuthorisation()->getGuid());
        if ($resolvedBuild->getForm(Form\MembersForms::FORM_PROFILE_EDIT)->isValid()) {
            /** @var ProfileManager $profileManager */
            $profileManager = $app['members.profile.manager'];
            /** @var Form\Entity\Profile $entity */
            $entity = $resolvedBuild->getEntity(Form\MembersForms::FORM_PROFILE_EDIT);
            $form = $resolvedBuild->getForm(Form\MembersForms::FORM_PROFILE_EDIT);
            $profileManager->saveProfileForm($entity, $form);
        }

        $template = $this->config->getTemplates('profile', 'edit');
        $html = $formsManager->renderForms($resolvedBuild, $app['twig'], $template);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Register a new member profile.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function registerProfile(Application $app, Request $request)
    {
        if ($app['members.session']->hasAuthorisation()) {
            return new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));
        }

        /** @var Form\Manager $formsManager */
        $formsManager = $app['members.forms.manager'];
        $resolvedForm = $formsManager->getFormProfileRegister($request, true);

        // Handle the form request data
        if ($resolvedForm->getForm(Form\MembersForms::FORM_PROFILE_REGISTER)->isValid()) {
            $app['members.oauth.provider.manager']->setProvider($app, 'local');

            $form = $resolvedForm->getForm(Form\MembersForms::FORM_PROFILE_REGISTER);
            /** @var Form\Entity\Profile $entity */
            $entity = $resolvedForm->getEntity(Form\MembersForms::FORM_PROFILE_REGISTER);

            /** @var ProfileManager $profileManager */
            $profileManager = $app['members.profile.manager'];
            $profileManager->saveProfileRegisterForm($entity, $form, $app['members.oauth.provider'], 'local');

            // Redirect to our profile page.
            $response = new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));

            return $response;
        }

        $context = ['transitional' => $app['members.session']->isTransitional()];
        $template = $this->config->getTemplates('profile', 'register');
        $html = $formsManager->renderForms($resolvedForm, $app['twig'], $template, $context);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Handles the user profile verification call.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function verifyProfile(Application $app, Request $request)
    {
        // Check the verification code
        $verification = new AccountVerification();
        $verification->validateCode($app['members.records'], $request->query->get('code'));

        if ($verification->isSuccess()) {
            $event = new MembersProfileEvent($verification->getAccount());
            $app['dispatcher']->dispatch(MembersEvents::MEMBER_PROFILE_VERIFY, $event);
        }

        $context = [
            'twigparent' => $this->config->getTemplates('profile', 'parent'),
            'code'       => $verification->getCode(),
            'success'    => $verification->isSuccess(),
            'message'    => $verification->getMessage(),
        ];

        $template = $this->config->getTemplates('profile', 'verify');
        $html = $app['twig']->render($template, $context);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * View a user's own profile.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return RedirectResponse|Response
     */
    public function viewProfile(Application $app, Request $request)
    {
        /** @var Session $session */
        $session = $app['members.session'];
        /** @var Form\Manager $formsManager */
        $formsManager = $app['members.forms.manager'];

        $memberSession = $session->getAuthorisation();
        if ($memberSession === null) {
            $app['session']->set(Authentication::FINAL_REDIRECT_KEY, $request->getUri());
            $app['members.feedback']->info('Login required to view your profile');

            return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
        }

        $template = $this->config->getTemplates('profile', 'view');
        $resolvedForm = $formsManager->getFormProfileEdit($request, true, $session->getAuthorisation()->getGuid());
        $html = $formsManager->renderForms($resolvedForm, $app['twig'], $template);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }
}
