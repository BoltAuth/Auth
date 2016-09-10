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
use Bolt\Extension\Bolt\Members\Form\Manager;
use Bolt\Extension\Bolt\Members\Form\MembersForms;
use Bolt\Extension\Bolt\Members\Form\ResolvedFormBuild;
use Bolt\Extension\Bolt\Members\Oauth2\Handler;
use Bolt\Extension\Bolt\Members\Storage;
use Carbon\Carbon;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Authentication implements ControllerProviderInterface
{
    const FINAL_REDIRECT_KEY = 'members.auth.redirect';

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

        $ctr
            ->after([$this, 'after'])
        ;

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
        if ($app['members.session']->getAuthorisation() === null) {
            $response->headers->clearCookie(Session::COOKIE_AUTHORISATION);

            return;
        }

        $cookie = $app['members.session']->getAuthorisation()->getCookie();
        if ($cookie === null) {
            $response->headers->clearCookie(Session::COOKIE_AUTHORISATION);
        } else {
            $response->headers->setCookie(new Cookie(Session::COOKIE_AUTHORISATION, $cookie, Carbon::now()->addSeconds(86400)));
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
        if ($app['members.session']->hasAuthorisation()) {
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

        // Set the return redirect.
        if ($app['members.config']->getRedirectLogin()) {
            $app['members.session']
                ->clearRedirects()
                ->addRedirect($app['members.config']->getRedirectLogin())
            ;
        } elseif ($request->headers->get('referer') !== $request->getUri()) {
            $app['members.session']
                ->clearRedirects()
                ->addRedirect($request->headers->get('referer', $app['resources']->getUrl('hosturl')))
            ;
        }

        $builder = $app['members.forms.manager']->getFormLogin($request);
        $oauthForm = $builder->getForm(MembersForms::FORM_LOGIN_OAUTH);
        if ($oauthForm->isValid()) {
            $response = $this->processOauthForm($app, $request, $oauthForm);
            if ($response instanceof Response) {
                return $response;
            }
        }

        $associateForm = $builder->getForm(MembersForms::FORM_ASSOCIATE);
        if ($associateForm->isValid()) {
            $response = $this->processOauthForm($app, $request, $associateForm);
            if ($response instanceof Response) {
                return $response;
            }
        }

        $passwordForm = $builder->getForm(MembersForms::FORM_LOGIN_PASSWORD);
        if ($passwordForm->isValid()) {
            $app['members.oauth.provider.manager']->setProvider($app, 'local');
            /** @var Handler\Local $handler */
            $handler = $app['members.oauth.handler'];
            $response = $handler->login($request, $passwordForm);
            if ($response instanceof Response) {
                return $response;
            }

            $app['members.feedback']->info('Login details are incorrect.');
        }
        $template = $this->config->getTemplate('authentication', 'login');
        $html = $app['members.forms.manager']->renderForms($builder, $app['twig'], $template);

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

        /** @var Handler\HandlerInterface $handler */
        $handler = $app['members.oauth.handler'];
        try {
            $handler->login($request);
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }

        return $app['members.session']->popRedirect()->getResponse();
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
        $app['members.oauth.provider.manager']->setProvider($app, 'local');

        /** @var Handler\HandlerInterface $handler */
        $handler = $app['members.oauth.provider.manager']->getProviderHandler();
        try {
            $handler->logout($request);
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }

        if ($app['members.config']->getRedirectLogout()) {
            return new RedirectResponse($app['members.config']->getRedirectLogout());
        }

        return $app['members.session']->popRedirect()->getResponse();
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
        $app['members.oauth.provider.manager']->setProvider($app, $providerName);
        /** @var Handler\HandlerInterface $handler */
        $handler = $app['members.oauth.handler'];
        try {
            $handler->process($request, 'authorization_code');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }
        $response = $app['members.session']->popRedirect()->getResponse();

        // Flush any pending redirects
        $app['members.session']->clearRedirects();

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
        if ($app['members.session']->hasAuthorisation()) {
            return new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));
        }
        /** @var Manager $formsManager */
        $formsManager = $app['members.forms.manager'];
        $response = new Response();
        //$context = ['stage' => null, 'email' => null, 'link' => $app['url_generator']->generate('authenticationLogin')];
        $context = new ParameterBag(['stage' => null, 'email' => null, 'link' => $app['url_generator']->generate('authenticationLogin')]);

        if ($request->query->has('code')) {
            $builder = $this->resetPasswordSubmit($app, $request, $context);
        } else {
            $builder = $this->resetPasswordRequest($app, $request, $context, $response);
        }

        $template = $this->config->getTemplate('authentication', 'recovery');
        $html = $formsManager->renderForms($builder, $app['twig'], $template, $context->all());
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
        $builder = $app['members.forms.manager']->getFormProfileRecovery($request);
        $form = $builder->getForm(MembersForms::FORM_PROFILE_RECOVER_SUBMIT);
        $context->set('stage', 'invalid');

        /** @var PasswordReset $passwordReset */
        $passwordReset = $app['session']->get(PasswordReset::COOKIE_NAME);
        if ($passwordReset === null || $passwordReset->validate($request) !== true) {
            return $builder;
        }

        $guid = $passwordReset->getGuid();
        $oauth = $app['members.records']->getOauthByGuid($guid);
        $provider = $app['members.records']->getProvision($guid, 'local');
        $context->set('stage', 'password');

        if ($form->isValid()) {
            // Password reset on an account that was registered via OAuth, sans a password
            if ($oauth === false) {
                $oauth = $app['members.records']->createOauth($guid, $guid, true);
            }
            if ($provider === false) {
                $app['members.records']->createProviderEntity($guid, 'local', $guid);
            }

            // Reset password
            $oauth->setPassword($form->get('password')->getData());

            $app['members.records']->saveOauth($oauth);
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
        $builder = $app['members.forms.manager']->getFormProfileRecovery($request);
        $form = $builder->getForm(MembersForms::FORM_PROFILE_RECOVER_REQUEST);
        $context->set('stage', 'email');

        if (!$form->isValid()) {
            return $builder;
        }

        $email = $form->get('email')->getData();
        $context->set('email', $email);
        $account = $app['members.records']->getAccountByEmail($email);
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
        $from = [$this->config->getNotificationEmail() => $this->config->getNotificationName()];
        $mailHtml = $this->getResetHtml($account, $passwordReset, $app['twig'], $app['resources']->getUrl('rooturl'));

        /** @var \Swift_Message $message */
        $message = $mailer->createMessage('message')
            ->setSubject($app['twig']->render($this->config->getTemplate('recovery', 'subject')))
            ->setBody(strip_tags($mailHtml))
            ->addPart($mailHtml, 'text/html')
        ;
        try {
            $message
                ->setFrom($from)
                ->setReplyTo($from)
                ->setTo($email)
            ;
            $failedRecipients = [];

            // Dispatch an event
            $event = new MembersNotificationEvent($message);
            $app['dispatcher']->dispatch(MembersEvents::MEMBER_NOTIFICATION_PRE_SEND, $event);

            $mailer->send($message, $failedRecipients);
        } catch (\Swift_RfcComplianceException $e) {
            // Dispatch an event
            $event = new MembersNotificationFailureEvent($message, $e);
            $app['dispatcher']->dispatch(MembersEvents::MEMBER_NOTIFICATION_FAILURE, $event);
        }

        $context->set('stage', 'submitted');

        return $builder;
    }

    /**
     * @param Storage\Entity\Account $account
     * @param PasswordReset          $passwordReset
     * @param \Twig_Environment      $twig
     * @param string                 $siteUrl
     *
     * @return string
     */
    private function getResetHtml(Storage\Entity\Account $account, PasswordReset $passwordReset, \Twig_Environment $twig, $siteUrl)
    {
        $query = http_build_query(['code' => $passwordReset->getQueryCode()]);
        $context = [
            'name'  => $account->getDisplayname(),
            'email' => $account->getEmail(),
            'link'  => sprintf('%s%s/reset?%s', $siteUrl, $this->config->getUrlAuthenticate(), $query),
        ];
        $mailHtml = $twig->render($this->config->getTemplate('recovery', 'body'), $context);

        return $mailHtml;
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
        $enabledProviders = $app['members.config']->getEnabledProviders();

        if (array_key_exists($providerName, $enabledProviders)) {
            $app['members.oauth.provider.manager']->setProvider($app, $providerName);

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
        if ($e instanceof IdentityProviderException) {
            // Thrown by the OAuth2 library
            $app['members.feedback']->error('An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } elseif ($e instanceof Exception\InvalidAuthorisationRequestException) {
            // Thrown deliberately internally
            $app['members.feedback']->error('An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } elseif ($e instanceof Exception\MissingAccountException) {
            // Thrown deliberately internally
            $app['members.feedback']->error('No registered account.');
            $response = new RedirectResponse($app['url_generator']->generate('membersProfileRegister'));
        } else {
            // Yeah, this can't be good…
            $app['members.feedback']->error('A server error occurred, we are very sorry and someone has been notified!');
            $response = new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Dispatch an event so that subscribers can extend exception handling
        if ($app['dispatcher']->hasListeners(ExceptionEvent::ERROR)) {
            try {
                $app['dispatcher']->dispatch(ExceptionEvent::ERROR, new ExceptionEvent($e));
            } catch (\Exception $e) {
                $app['logger.system']->critical('[Members][Controller] Event dispatcher had an error', ['event' => 'exception', 'exception' => $e]);
            }
        }

        $app['members.feedback']->debug($e->getMessage());
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
        $ext = $app['extensions']->get('Bolt/Members');
        $app['twig.loader.bolt_filesystem']->addPath($ext->getBaseDirectory()->getFullPath() . '/templates/error/');
        $context = [
            'parent'    => $app['members.config']->getTemplate('error', 'parent'),
            'feedback'  => $app['members.feedback']->get(),
            'exception' => $e,
        ];
        $html = $app['twig']->render($this->config->getTemplate('error', 'error'), $context);

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
        $app['members.feedback']->debug($msg);

        return false;
    }
}
