<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
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
            ->method('GET|POST')
        ;

        $ctr->match('/profile/register', [$this, 'registerProfile'])
            ->bind('membersProfileRegister')
            ->method('GET|POST')
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
        /** @var Form\Type\ProfileEditType $profileFormType */
        $profileFormType = $app['members.form.components']['type']['profile'];

        $memberSession = $session->getAuthorisation();

        if ($memberSession === null) {
            $app['session']->set(Authentication::FINAL_REDIRECT_KEY, $request->getUri());
            $app['members.feedback']->info('Login required to edit your profile');

            return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
        }

        $profileFormType->setRequirePassword(false);
        $resolvedForm = $formsManager->getFormProfileEdit($request, true);

        // Handle the form request data
        if ($resolvedForm->getForm('form_profile')->isValid()) {
            /** @var Form\ProfileEditForm $profileForm */
            $profileForm = $app['members.form.profile'];
            $profileForm->saveForm($app['members.records'], $app['dispatcher']);
        }

        $template = $this->config->getTemplates('profile', 'edit');
        $html = $formsManager->renderForms($resolvedForm, $template);

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
        /** @var Form\Manager $formsManager */
        $formsManager = $app['members.forms.manager'];
        $resolvedForm = $formsManager->getFormProfileRegister($request, true);

        // Handle the form request data
        if ($resolvedForm->getForm('form_register')->isValid()) {
            $app['members.oauth.provider.manager']->setProvider($app, 'local');
            /** @var Form\ProfileRegisterForm $registerForm */
            $registerForm = $app['members.form.register'];
            $registerForm
                ->setProvider($app['members.oauth.provider'])
                ->saveForm($app['members.records'], $app['dispatcher'])
            ;
            // Redirect to our profile page.
            $response =  new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));

            return $response;
        }

        $context = ['transitional' => $app['members.session']->isTransitional()];
        $template = $this->config->getTemplates('profile', 'register');
        $html = $formsManager->renderForms($resolvedForm, $template, $context);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }
}
