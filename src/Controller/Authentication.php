<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\AccessControl\Validator\PasswordReset;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersExceptionEvent as ExceptionEvent;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationFailureEvent;
use Bolt\Extension\Bolt\Members\Exception;
use Bolt\Extension\Bolt\Members\Form\MembersForms;
use Bolt\Extension\Bolt\Members\Form\ResolvedFormBuild;
use Bolt\Extension\Bolt\Members\Oauth2\Handler;
use Bolt\Extension\Bolt\Members\Storage;
use Carbon\Carbon;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swift_Mime_Message as SwiftMimeMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Authentication controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Authentication extends AbstractController
{
    const FINAL_REDIRECT_KEY = 'members.auth.redirect';

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = parent::connect($app);

        // Member login
        $ctr->match('/login', [$this, 'login'])
            ->bind('authenticationLogin')
            ->method('GET|POST')
        ;

        // Member login
        $ctr->match('/login/process', [$this, 'processLogin'])
            ->bind('authenticationProcessLogin')
            ->method('GET')
        ;

        // Member logout
        $ctr->match('/logout', [$this, 'logout'])
            ->bind('authenticationLogout')
            ->method('GET')
        ;

        // OAuth callback URI
        $ctr->match('/oauth2/callback', [$this, 'oauthCallback'])
            ->bind('authenticationCallback')
            ->method('GET')
        ;

        $ctr->match('/reset', [$this, 'resetPassword'])
            ->bind('authenticationPasswordReset')
            ->method('GET|POST')
        ;

        // Own the rest of the base route
        $ctr->match('/', [$this, 'defaultRoute'])
            ->bind('authenticationDefaultBase')
        ;

        $ctr->match('/{url}', [$this, 'defaultRoute'])
            ->bind('authenticationDefault')
            ->assert('url', '.+')
        ;

        $ctr->after([$this, 'after']);

        return $ctr;
    }

    /**
     * Middleware to modify the Response before it is sent to the client.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function after(Request $request, Response $response)
    {
        if ($this->getMembersSession()->getAuthorisation() === null) {
            $response->headers->clearCookie(Session::COOKIE_AUTHORISATION);

            return;
        }

        $cookie = $this->getMembersSession()->getAuthorisation()->getCookie();
        if ($cookie === null) {
            $response->headers->clearCookie(Session::COOKIE_AUTHORISATION);
        } else {
            $response->headers->setCookie(new Cookie(Session::COOKIE_AUTHORISATION, $cookie, Carbon::now()->addDays(7)));
        }

        $request->attributes->set('members-cookies', 'set');
    }

    /**
     * Default catch-all route.
     *
     * @param Application $app
     *
     * @return RedirectResponse
     */
    public function defaultRoute(Application $app)
    {
        if ($this->getMembersSession()->hasAuthorisation()) {
            return new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));
        }

        return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
    }

    /**
     * Login route.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function login(Application $app, Request $request)
    {
        $this->assertSecure($app, $request);
        $this->assertIncompetentFuckingStupidity($app['members.config']);

        $config = $this->getMembersConfig();
        $loginRedirect = $config->getRedirectLogin();
        $homepage = $app['url_generator']->generate('homepage');
        $referer = $request->headers->get('referer');

        // Set the return redirect.
        if ($request->get('redirect')) {
            $redirect = $request->get('redirect');
        } elseif ($loginRedirect) {
            $redirect = $loginRedirect;
        } elseif ($referer !== $request->getUri()) {
            $redirect = $request->headers->get('referer');
        } else {
            $redirect = $homepage;
        }
        $this->getMembersSession()
            ->clearRedirects()
            ->addRedirect($redirect)
        ;

        $builder = $this->getMembersFormsManager()->getFormLogin($request);
        /** @var Form $oauthForm */
        $oauthForm = $builder->getForm(MembersForms::LOGIN_OAUTH);
        if ($oauthForm->isValid()) {
            $response = $this->processOauthForm($app, $request, $oauthForm);
            if ($response instanceof Response) {
                return $response;
            }
        }

        /** @var Form $associateForm */
        $associateForm = $builder->getForm(MembersForms::ASSOCIATE);
        if ($associateForm->isValid()) {
            $response = $this->processOauthForm($app, $request, $associateForm);
            if ($response instanceof Response) {
                return $response;
            }
        }

        /** @var Form $passwordForm */
        $passwordForm = $builder->getForm(MembersForms::LOGIN_PASSWORD);
        if ($passwordForm->isValid()) {
            $this->getMembersOauthProviderManager()->setProvider($app, 'local');

            /** @var Handler\Local $handler */
            $handler = $this->getMembersOauthHandler();
            $handler->setSubmittedForm($passwordForm);

            // Initial login checks
            $response = $handler->login($request);
            if ($response instanceof Response) {
                return $response;
            }

            // Process and check password, initiate the session is successful
            $response = $handler->process($request);
            if ($response instanceof Response) {
                return $response;
            }

            $this->getMembersFeedback()->info('Login details are incorrect.');
        }
        $template = $config->getTemplate('authentication', 'login');
        $html = $this->getMembersFormsManager()->renderForms($builder, $app['twig'], $template);

        return new Response($html);
    }

    /**
     * Login processing route.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function processLogin(Application $app, Request $request)
    {
        $this->assertSecure($app, $request);

        try {
            $this->getMembersOauthHandler()->login($request);
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }

        return $this->getMembersSession()->popRedirect()->getResponse();
    }

    /**
     * Login route.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function logout(Application $app, Request $request)
    {
        $this->getMembersOauthProviderManager()->setProvider($app, 'local');

        /** @var Handler\HandlerInterface $handler */
        $handler = $this->getMembersOauthProviderManager()->getProviderHandler();
        try {
            $handler->logout($request);
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }

        $config = $this->getMembersConfig();
        if ($config->getRedirectLogout()) {
            return new RedirectResponse($config->getRedirectLogout());
        }

        return $this->getMembersSession()->popRedirect()->getResponse();
    }

    /**
     * Login route.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function oauthCallback(Application $app, Request $request)
    {
        $providerName = $request->query->get('provider');
        $this->getMembersOauthProviderManager()->setProvider($app, $providerName);

        try {
            $this->getMembersOauthHandler()->process($request, 'authorization_code');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }
        $response = $this->getMembersSession()->popRedirect()->getResponse();

        // Flush any pending redirects
        $this->getMembersSession()->clearRedirects();

        return $response;
    }

    /**
     * Password reset route.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return RedirectResponse|Response
     */
    public function resetPassword(Application $app, Request $request)
    {
        if ($this->getMembersSession()->hasAuthorisation()) {
            return new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));
        }

        $response = new Response();
        $context = new ParameterBag(['stage' => null, 'email' => null, 'link' => $app['url_generator']->generate('authenticationLogin')]);

        if ($request->query->has('code')) {
            $builder = $this->resetPasswordSubmit($app, $request, $context);
        } else {
            $builder = $this->resetPasswordRequest($app, $request, $context, $response);
        }

        $template = $this->getMembersConfig()->getTemplate('authentication', 'recovery');
        $html = $this->getMembersFormsManager()->renderForms($builder, $app['twig'], $template, $context->all());
        $response->setContent(new \Twig_Markup($html, 'UTF-8'));

        return $response;
    }

    /**
     * Process reset request.
     *
     * @param Application  $app
     * @param Request      $request
     * @param ParameterBag $context
     *
     * @return ResolvedFormBuild
     */
    private function resetPasswordSubmit(Application $app, Request $request, ParameterBag $context)
    {
        $builder = $this->getMembersFormsManager()->getFormProfileRecovery($request);
        $form = $builder->getForm(MembersForms::PROFILE_RECOVERY_SUBMIT);
        $context->set('stage', 'invalid');

        /** @var PasswordReset $passwordReset */
        $passwordReset = $app['session']->get(PasswordReset::COOKIE_NAME);
        if ($passwordReset === null || $passwordReset->validate($request) !== true) {
            return $builder;
        }

        $guid = $passwordReset->getGuid();
        $oauth = $this->getMembersRecords()->getOauthByGuid($guid);
        $provider = $this->getMembersRecords()->getProvision($guid, 'local');
        $context->set('stage', 'password');

        if ($form->isValid()) {
            // Password reset on an account that was registered via OAuth, sans a password
            if ($oauth === false) {
                $oauth = $this->getMembersRecords()->createOauth($guid, $guid, true);
            }
            if ($provider === false) {
                $this->getMembersRecords()->createProviderEntity($guid, 'local', $guid);
            }

            // Reset password
            $oauth->setPassword($form->get('password')->getData());

            $this->getMembersRecords()->saveOauth($oauth);
            $app['session']->remove(PasswordReset::COOKIE_NAME);

            $context->set('stage', 'reset');
        }

        return $builder;
    }

    /**
     * Handle new request.
     *
     * @param Application  $app
     * @param Request      $request
     * @param ParameterBag $context
     * @param Response     $response
     *
     * @return ResolvedFormBuild
     */
    private function resetPasswordRequest(Application $app, Request $request, ParameterBag $context, Response $response)
    {
        $builder = $this->getMembersFormsManager()->getFormProfileRecovery($request);
        $form = $builder->getForm(MembersForms::PROFILE_RECOVERY_REQUEST);
        $context->set('stage', 'email');

        if (!$form->isValid()) {
            return $builder;
        }

        $email = $form->get('email')->getData();
        $context->set('email', $email);
        $account = $this->getMembersRecords()->getAccountByEmail($email);
        if ($account === false) {
            return $builder;
        }

        // Create and store the password reset in the session
        $passwordReset = (new PasswordReset())
            ->setGuid($account->getGuid())
            ->setCookieValue()
            ->setQueryCode()
        ;
        $app['session']->set(PasswordReset::COOKIE_NAME, $passwordReset);

        // Add cookie to response
        $cookie = new Cookie(PasswordReset::COOKIE_NAME, $passwordReset->getCookieValue(), Carbon::now()->addHour(1));
        $response->headers->setCookie($cookie);

        /** @var \Swift_Mailer $mailer */
        $mailer = $app['mailer'];
        $config = $this->getMembersConfig();
        $from = [$config->getNotificationEmail() => $config->getNotificationName()];
        $subject = $app['twig']->render($config->getTemplate('recovery', 'subject'), ['member' => $account]);
        /** @var \Swift_Message $message */
        $message = $mailer->createMessage('message');

        try {
            $message
                ->setTo($email)
                ->setFrom($from)
                ->setReplyTo($from)
                ->setSubject($subject)
                ->setMaxLineLength(10)
            ;
            $this->setBody($message, $account, $passwordReset, $app['twig']);

            // Dispatch an event
            $event = new MembersNotificationEvent($message);
            $app['dispatcher']->dispatch(MembersEvents::MEMBER_PROFILE_RESET, $event);
        } catch (\Swift_RfcComplianceException $e) {
            // Dispatch an event
            $event = new MembersNotificationFailureEvent($message, $e);
            $app['dispatcher']->dispatch(MembersEvents::MEMBER_NOTIFICATION_FAILURE, $event);
        }

        $context->set('stage', 'submitted');

        return $builder;
    }

    /**
     * Generate the HTML and/or text for the password reset email.
     *
     * @param SwiftMimeMessage       $message
     * @param Storage\Entity\Account $account
     * @param PasswordReset          $passwordReset
     * @param \Twig_Environment      $twig
     */
    private function setBody(SwiftMimeMessage $message, Storage\Entity\Account $account, PasswordReset $passwordReset, \Twig_Environment $twig)
    {
        $app = $this->getContainer();
        $query = [
            'code' => $passwordReset->getQueryCode()
        ];
        $link = $app['url_generator']->generate('authenticationPasswordReset', $query, UrlGeneratorInterface::ABSOLUTE_URL);
        $context = [
            'name'   => $account->getDisplayname(),
            'email'  => $account->getEmail(),
            'link'   => $link,
            'member' => $account,
        ];

        $config = $this->getMembersConfig();
        $template = $config->getTemplate('recovery', 'text');
        $bodyText = $twig->render($template, $context);
        $message->setBody($bodyText);

        if ($config->getNotificationEmailFormat() !== 'text') {
            $template = $config->getTemplate('recovery', 'html');
            $bodyHtml = $twig->render($template, $context);
            /** @var \Swift_Message $message */
            $message->addPart($bodyHtml, 'text/html');
        }
    }

    /**
     * Helper to process an OAuth login form.
     *
     * @param Application $app
     * @param Request     $request
     * @param Form        $form
     *
     * @throws Exception\InvalidProviderException
     *
     * @return null|Response
     */
    private function processOauthForm(Application $app, Request $request, Form $form)
    {
        $providerName = $form->getClickedButton()->getName();
        $enabledProviders = $this->getMembersConfig()->getEnabledProviders();

        if (array_key_exists($providerName, $enabledProviders)) {
            $this->getMembersOauthProviderManager()->setProvider($app, $providerName);

            return $this->processLogin($app, $request);
        }

        return null;
    }

    /**
     * Get an exception state's HTML response page.
     *
     * @param Application $app
     * @param \Exception  $e
     *
     * @return Response
     */
    private function getExceptionResponse(Application $app, \Exception $e)
    {
        $dispatcher = $app['dispatcher'];

        if ($e instanceof IdentityProviderException) {
            // Thrown by the OAuth2 library
            $this->getMembersFeedback()->error('An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } elseif ($e instanceof Exception\InvalidAuthorisationRequestException) {
            // Thrown deliberately internally
            $this->getMembersFeedback()->error('An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } elseif ($e instanceof Exception\MissingAccountException) {
            // Thrown deliberately internally
            $this->getMembersFeedback()->error('No registered account.');
            $response = new RedirectResponse($app['url_generator']->generate('membersProfileRegister'));
        } else {
            // Yeah, this can't be good…
            $this->getMembersFeedback()->error('A server error occurred, we are very sorry and someone has been notified!');
            $response = new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Dispatch an event so that subscribers can extend exception handling
        if ($dispatcher->hasListeners(ExceptionEvent::ERROR)) {
            try {
                $dispatcher->dispatch(ExceptionEvent::ERROR, new ExceptionEvent($e));
            } catch (\Exception $e) {
                $this->getMembersFeedback()->debug(sprintf('Event dispatcher "%s" error: %s', ExceptionEvent::ERROR, $e->getMessage()));
                $app['logger.system']->critical('[Members][Controller] Event dispatcher had an error', ['event' => 'exception', 'exception' => $e]);
            }
        }

        $this->getMembersFeedback()->debug($e->getMessage());
        $response->setContent($this->displayExceptionPage($app, $e));

        return $response;
    }

    /**
     * Render one of our exception pages.
     *
     * @param Application $app
     * @param \Exception  $e
     *
     * @return \Twig_Markup
     */
    private function displayExceptionPage(Application $app, \Exception $e)
    {
        $config = $this->getMembersConfig();
        $ext = $app['extensions']->get('Bolt/Members');
        $app['twig.loader.bolt_filesystem']->addPath($ext->getBaseDirectory()->getFullPath() . '/templates/error/');
        $context = [
            'parent'    => $config->getTemplate('error', 'parent'),
            'feedback'  => $this->getMembersFeedback()->get(),
            'exception' => $e,
        ];
        $html = $app['twig']->render($config->getTemplate('error', 'error'), $context);

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * Log a warning and debug notice if this route is not HTTPS.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return bool
     */
    private function assertSecure(Application $app, Request $request)
    {
        if ($request->isSecure()) {
            return true;
        }

        $msg = sprintf("Login route '%s' is not being served over HTTPS. This is insecure and vulnerable!", $request->getPathInfo());
        $app['logger.system']->critical(sprintf('[Members][Controller]: %s', $msg), ['event' => 'extensions']);
        $this->getMembersFeedback()->debug($msg);

        return false;
    }

    /**
     * Remind people how fucking stupid they are to use WP-OAuth!
     *
     * @param Config $config
     */
    private function assertIncompetentFuckingStupidity(Config $config)
    {
        if (!$config->hasProvider('wpoauth')) {
            return;
        }

        $provider = $config->getProvider('wpoauth');
        if ($provider->isEnabled()) {
            $this->getMembersFeedback()->info(sprintf('WARNING: One of the configured OAuth providers, "%s", uses WP-OAuth.', $provider->getLabelSignIn()));
            $this->getMembersFeedback()->info('WP-Oauth is completely unsafe, and insecure. Choosing another provider would be very sensible!');
        }
    }
}
