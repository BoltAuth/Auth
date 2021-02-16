<?php

namespace Bolt\Extension\BoltAuth\Auth\Controller;

use Bolt\Extension\BoltAuth\Auth\AccessControl\Session;
use Bolt\Extension\BoltAuth\Auth\AccessControl\Validator\AccountVerification;
use Bolt\Extension\BoltAuth\Auth\Event\AuthEvents;
use Bolt\Extension\BoltAuth\Auth\Event\AuthProfileEvent;
use Bolt\Extension\BoltAuth\Auth\Exception\AccountVerificationException;
use Bolt\Extension\BoltAuth\Auth\Form;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider\ResourceOwnerInterface;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Auth controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Auth extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = parent::connect($app);

        $ctr->match('/profile/edit', [$this, 'editProfile'])
            ->bind('authProfileEdit')
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/profile/register', [$this, 'registerProfile'])
            ->bind('authProfileRegister')
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/profile/verify', [$this, 'verifyProfile'])
            ->bind('authProfileVerify')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/profile/view', [$this, 'viewProfile'])
            ->bind('authProfileView')
            ->method(Request::METHOD_GET)
        ;

        // Own the rest of the base route
        $ctr->match('/', [$this, 'defaultRoute'])
            ->bind('authDefaultBase')
        ;

        $ctr->match('/{url}', [$this, 'defaultRoute'])
            ->bind('authDefault')
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
        $session = $this->getAuthSession();
        if ($session->getAuthorisation() === null) {
            $response->headers->clearCookie(Session::COOKIE_AUTHORISATION);

            return;
        }

        $cookie = $session->getAuthorisation()->getCookie();
        if ($cookie === null) {
            $response->headers->clearCookie(Session::COOKIE_AUTHORISATION);
        }

        $request->attributes->set('auth-cookies', 'set');
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
        if ($this->getAuthSession()->hasAuthorisation()) {
            return new RedirectResponse($app['url_generator']->generate('authProfileEdit'));
        }

        return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
    }

    /**
     * Edit an existing auth profile.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function editProfile(Application $app, Request $request)
    {
        $authSession = $this->getAuthSession()->getAuthorisation();
        if ($authSession === null) {
            $app['session']->set(Authentication::FINAL_REDIRECT_KEY, $request->getUri());
            $this->getAuthFeedback()->error('Login required to edit your profile');

            return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
        }
        $this->getAuthFeedback()->debug(sprintf('Editing profile for account %s (%s)', $authSession->getAccount()->getEmail(), $authSession->getGuid()));

        // Handle the form request data
        $resolvedBuild = $this->getAuthFormsManager()->getFormProfileEdit($request, true, $authSession->getGuid());
        if ($resolvedBuild->getForm(Form\AuthForms::PROFILE_EDIT)->isValid()) {
            /** @var Form\Entity\Profile $entity */
            $entity = $resolvedBuild->getEntity(Form\AuthForms::PROFILE_EDIT);
            $form = $resolvedBuild->getForm(Form\AuthForms::PROFILE_EDIT);
            $this->getAuthRecordsProfile()->saveProfileForm($entity, $form);
        }

        $template = $this->getAuthConfig()->getTemplate('profile', 'edit');
        $html = $this->getAuthFormsManager()->renderForms($resolvedBuild, $app['twig'], $template);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Register a new auth profile.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function registerProfile(Application $app, Request $request)
    {
        if ($this->getAuthSession()->hasAuthorisation()) {
            return new RedirectResponse($app['url_generator']->generate('authProfileEdit'));
        }

        // If registration is closed, just return a 404
        if ($this->getAuthConfig()->isRegistrationOpen() === false) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        $session = $app['auth.session'];
        $oauthAuthFinish = $session->hasAttribute(Session::SESSION_ATTRIBUTE_OAUTH_DATA)
            ? $session->getAttribute(Session::SESSION_ATTRIBUTE_OAUTH_DATA)
            : null
        ;

        $builder = $this->getAuthFormsManager()->getFormProfileRegister($request, true);
        $form = $builder->getForm(Form\AuthForms::PROFILE_REGISTER);

        // Handle the form request data
        if ($form->isValid()) {
            $this->getAuthOauthProviderManager()->setProvider($app, 'local');
            /** @var Form\Entity\Profile $entity */
            $entity = $builder->getEntity(Form\AuthForms::PROFILE_REGISTER);

            try {
                $this->getAuthRecordsProfile()->saveProfileRegisterForm($entity, $form, $this->getAuthOauthProvider(), 'local');
                $this->getAuthFeedback()->debug(sprintf('Registered account %s (%s)', $entity->getEmail(), $entity->getGuid()));

                if ($oauthAuthFinish) {
                    /** @var ResourceOwnerInterface $resourceOwner */
                    $resourceOwner = $oauthAuthFinish['resourceOwner'];
                    $providerName = $oauthAuthFinish['providerName'];
                    $guid = $this->getAuthSession()->getAuthorisation()->getGuid();
                    $this->getAuthRecords()->createProviderEntity($guid, $providerName, $resourceOwner->getId());
                    $session->removeAttribute(Session::SESSION_ATTRIBUTE_OAUTH_DATA);
                }

                // if redirect:register is set in extension configuration redirect there
                if ($this->getAuthConfig()->getRedirectRegister() != null) {
                    return new RedirectResponse($this->getAuthConfig()->getRedirectRegister());
                }

                // Redirect to our profile page.
                $response = new RedirectResponse($app['url_generator']->generate('authProfileEdit'));

                return $response;
            } catch (UniqueConstraintViolationException $e) {
                // New profile request has an existing email address
                $form->get('email')->addError(new FormError('Email address is already registered.'));
            }
        }

        $context = [
            'auth'       => $oauthAuthFinish ? $oauthAuthFinish['resourceOwner'] : null,
            'transitional' => $this->getAuthSession()->isTransitional(),
        ];
        $template = $this->getAuthConfig()->getTemplate('profile', 'register');
        $html = $this->getAuthFormsManager()->renderForms($builder, $app['twig'], $template, $context);

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
        $session = $app['session'];

        $code = $request->query->get('code');
        $sessionKey = 'auth.account.verify';

        $verification = $session->remove($sessionKey);
        if ($verification instanceof AccountVerification) {
            // We have a successful verification
            return $this->getVerifyResponse($app, $verification);
        }

        if ($code === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        // Check the verification code
        $verification = new AccountVerification();

        try {
            $verification->validateCode($this->getAuthRecords(), $code);
            $this->getAuthFeedback()->debug(sprintf('Verification completed with result: %s', $verification->isSuccess()));
        } catch (AccountVerificationException $e) {
            $this->getAuthFeedback()->debug(sprintf('Verification failed with result: %s', $e->getMessage()));
        }

        if ($verification->isSuccess()) {
            $event = new AuthProfileEvent($verification->getAccount());
            $app['dispatcher']->dispatch(AuthEvents::AUTH_PROFILE_VERIFY, $event);
            $session->set($sessionKey, $verification);

            // if redirect:verify is set in extension configuration redirect there
            if ($this->getAuthConfig()->getRedirectVerify() != null) {
                return new RedirectResponse($this->getAuthConfig()->getRedirectVerify());
            }

            return new RedirectResponse($app['url_generator']->generate('authProfileVerify'));
        }

        return $this->getVerifyResponse($app, $verification);
    }

    /**
     * @param Application         $app
     * @param AccountVerification $verification
     *
     * @return Response
     */
    private function getVerifyResponse(Application $app, AccountVerification $verification)
    {
        $config = $this->getAuthConfig();
        $context = [
            'twigparent' => $config->getTemplate('profile', 'parent'),
            'code'       => $verification->getCode(),
            'success'    => $verification->isSuccess(),
            'message'    => $verification->getMessage(),
            'feedback'   => $this->getAuthFeedback(),
            'providers'  => $config->getEnabledProviders(),
            'templates'  => [
                'feedback' => $config->getTemplate('feedback', 'feedback'),
            ],
        ];

        $context['templates']['feedback'] = $config->getTemplate('feedback', 'feedback');

        $template = $config->getTemplate('profile', 'verify');
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
            $authSession = $this->getAuthSession()->getAuthorisation();
            if ($authSession === null) {
                $app['session']->set(Authentication::FINAL_REDIRECT_KEY, $request->getUri());
                $this->getAuthFeedback()->error('Login required to view your profile');

                return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
            }

            $guid = $this->getAuthSession()->getAuthorisation()->getGuid();
        }

        $template = $this->getAuthConfig()->getTemplate('profile', 'view');
        $builder = $this->getAuthFormsManager()->getFormProfileView($request, true, $guid);

        /** @var Entity\Account $account */
        $account = $builder->getEntity(Form\AuthForms::PROFILE_VIEW);
        $this->getAuthFeedback()->debug(sprintf('Viewing profile account %s (%s)', $account->getEmail(), $account->getGuid()));

        $html = $this->getAuthFormsManager()->renderForms($builder, $app['twig'], $template);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }
}
