<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\AccessControl\Validator\AccountVerification;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Storage\FormEntityHandler;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Membership controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Membership implements ControllerProviderInterface
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
            /** @var FormEntityHandler $profileRecords */
            $profileRecords = $app['members.records.profile'];
            /** @var Form\Entity\Profile $entity */
            $entity = $resolvedBuild->getEntity(Form\MembersForms::FORM_PROFILE_EDIT);
            $form = $resolvedBuild->getForm(Form\MembersForms::FORM_PROFILE_EDIT);
            $profileRecords->saveProfileForm($entity, $form);
        }

        $template = $this->config->getTemplate('profile', 'edit');
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

        // If registration is closed, just return a 404
        if ($app['members.config']->isRegistrationOpen() === false) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        /** @var Form\Manager $formsManager */
        $formsManager = $app['members.forms.manager'];
        $builder = $formsManager->getFormProfileRegister($request, true);
        $form = $builder->getForm(Form\MembersForms::FORM_PROFILE_REGISTER);

        // Handle the form request data
        if ($form->isValid()) {
            $app['members.oauth.provider.manager']->setProvider($app, 'local');

            /** @var Form\Entity\Profile $entity */
            $entity = $builder->getEntity(Form\MembersForms::FORM_PROFILE_REGISTER);

            /** @var FormEntityHandler $profileRecords */
            $profileRecords = $app['members.records.profile'];
            try {
                $profileRecords->saveProfileRegisterForm($entity, $form, $app['members.oauth.provider'], 'local');

                // Redirect to our profile page.
                $response = new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));

                return $response;
            } catch (UniqueConstraintViolationException $e) {
                // New profile request has an existing email address
                $form->get('email')->addError(new FormError('Email address is already registered.'));
            }
        }

        $context = ['transitional' => $app['members.session']->isTransitional()];
        $template = $this->config->getTemplate('profile', 'register');
        $html = $formsManager->renderForms($builder, $app['twig'], $template, $context);

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
            'twigparent' => $this->config->getTemplate('profile', 'parent'),
            'code'       => $verification->getCode(),
            'success'    => $verification->isSuccess(),
            'message'    => $verification->getMessage(),
        ];

        $template = $this->config->getTemplate('profile', 'verify');
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
        $guid = $request->query->get('id');
        if ($guid !== null && !Uuid::isValid($guid)) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        if ($guid === null) {
            /** @var Session $session */
            $session = $app['members.session'];

            $memberSession = $session->getAuthorisation();
            if ($memberSession === null) {
                $app['session']->set(Authentication::FINAL_REDIRECT_KEY, $request->getUri());
                $app['members.feedback']->info('Login required to view your profile');

                return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
            }

            $guid = $session->getAuthorisation()->getGuid();
        }

        /** @var Form\Manager $formsManager */
        $formsManager = $app['members.forms.manager'];

        $template = $this->config->getTemplate('profile', 'view');
        $builder = $formsManager->getFormProfileView($request, true, $guid);
        $html = $formsManager->renderForms($builder, $app['twig'], $template);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }
}
