<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\AccessControl\Validator\AccountVerification;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Exception\AccountVerificationException;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\ResourceOwnerInterface;
use Bolt\Extension\Bolt\Members\Storage\Entity;
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
 * Membership controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Membership extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = parent::connect($app);

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
        $session = $this->getMembersSession();
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
        if ($this->getMembersSession()->hasAuthorisation()) {
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
        $memberSession = $this->getMembersSession()->getAuthorisation();
        if ($memberSession === null) {
            $app['session']->set(Authentication::FINAL_REDIRECT_KEY, $request->getUri());
            $this->getMembersFeedback()->info('Login required to edit your profile');

            return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
        }
        $this->getMembersFeedback()->debug(sprintf('Editing profile for account %s (%s)', $memberSession->getAccount()->getEmail(), $memberSession->getGuid()));

        // Handle the form request data
        $resolvedBuild = $this->getMembersFormsManager()->getFormProfileEdit($request, true, $memberSession->getGuid());
        if ($resolvedBuild->getForm(Form\MembersForms::PROFILE_EDIT)->isValid()) {
            /** @var Form\Entity\Profile $entity */
            $entity = $resolvedBuild->getEntity(Form\MembersForms::PROFILE_EDIT);
            $form = $resolvedBuild->getForm(Form\MembersForms::PROFILE_EDIT);
            $this->getMembersRecordsProfile()->saveProfileForm($entity, $form);
        }

        $template = $this->getMembersConfig()->getTemplate('profile', 'edit');
        $html = $this->getMembersFormsManager()->renderForms($resolvedBuild, $app['twig'], $template);

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
        if ($this->getMembersSession()->hasAuthorisation()) {
            return new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));
        }

        // If registration is closed, just return a 404
        if ($this->getMembersConfig()->isRegistrationOpen() === false) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        $session = $app['members.session'];
        $oauthMemberFinish = $session->hasAttribute(Session::SESSION_ATTRIBUTE_OAUTH_DATA)
            ? $session->getAttribute(Session::SESSION_ATTRIBUTE_OAUTH_DATA)
            : null
        ;

        $builder = $this->getMembersFormsManager()->getFormProfileRegister($request, true);
        $form = $builder->getForm(Form\MembersForms::PROFILE_REGISTER);

        // Handle the form request data
        if ($form->isValid()) {
            $this->getMembersOauthProviderManager()->setProvider($app, 'local');
            /** @var Form\Entity\Profile $entity */
            $entity = $builder->getEntity(Form\MembersForms::PROFILE_REGISTER);

            try {
                $this->getMembersRecordsProfile()->saveProfileRegisterForm($entity, $form, $this->getMembersOauthProvider(), 'local');
                $this->getMembersFeedback()->debug(sprintf('Registered account %s (%s)', $entity->getEmail(), $entity->getGuid()));

                if ($oauthMemberFinish) {
                    /** @var ResourceOwnerInterface $resourceOwner */
                    $resourceOwner = $oauthMemberFinish['resourceOwner'];
                    $providerName = $oauthMemberFinish['providerName'];
                    $guid = $this->getMembersSession()->getAuthorisation()->getGuid();
                    $this->getMembersRecords()->createProviderEntity($guid, $providerName, $resourceOwner->getId());
                    $session->removeAttribute(Session::SESSION_ATTRIBUTE_OAUTH_DATA);
                }

                // Redirect to our profile page.
                $response = new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));

                return $response;
            } catch (UniqueConstraintViolationException $e) {
                // New profile request has an existing email address
                $form->get('email')->addError(new FormError('Email address is already registered.'));
            }
        }

        $context = [
            'member'       => $oauthMemberFinish ? $oauthMemberFinish['resourceOwner'] : null,
            'transitional' => $this->getMembersSession()->isTransitional(),
        ];
        $template = $this->getMembersConfig()->getTemplate('profile', 'register');
        $html = $this->getMembersFormsManager()->renderForms($builder, $app['twig'], $template, $context);

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
        $sessionKey = 'members.account.verify';

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
            $verification->validateCode($this->getMembersRecords(), $code);
            $this->getMembersFeedback()->debug(sprintf('Verification completed with result: %s', $verification->isSuccess()));
        } catch (AccountVerificationException $e) {
            $this->getMembersFeedback()->debug(sprintf('Verification failed with result: %s', $e->getMessage()));
        }

        if ($verification->isSuccess()) {
            $event = new MembersProfileEvent($verification->getAccount());
            $app['dispatcher']->dispatch(MembersEvents::MEMBER_PROFILE_VERIFY, $event);
            $session->set($sessionKey, $verification);

            return new RedirectResponse($app['url_generator']->generate('membersProfileVerify'));
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
        $config = $this->getMembersConfig();
        $context = [
            'twigparent' => $config->getTemplate('profile', 'parent'),
            'code'       => $verification->getCode(),
            'success'    => $verification->isSuccess(),
            'message'    => $verification->getMessage(),
            'feedback'   => $this->getMembersFeedback(),
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
            $memberSession = $this->getMembersSession()->getAuthorisation();
            if ($memberSession === null) {
                $app['session']->set(Authentication::FINAL_REDIRECT_KEY, $request->getUri());
                $this->getMembersFeedback()->info('Login required to view your profile');

                return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
            }

            $guid = $this->getMembersSession()->getAuthorisation()->getGuid();
        }

        $template = $this->getMembersConfig()->getTemplate('profile', 'view');
        $builder = $this->getMembersFormsManager()->getFormProfileView($request, true, $guid);

        /** @var Entity\Account $account */
        $account = $builder->getEntity(Form\MembersForms::PROFILE_VIEW);
        $this->getMembersFeedback()->debug(sprintf('Viewing profile account %s (%s)', $account->getEmail(), $account->getGuid()));

        $html = $this->getMembersFormsManager()->renderForms($builder, $app['twig'], $template);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }
}
