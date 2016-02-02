<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersExceptionEvent as ExceptionEvent;
use Bolt\Extension\Bolt\Members\Exception\InvalidAuthorisationRequestException;
use Bolt\Extension\Bolt\Members\Exception\MissingAccountException;
use Bolt\Extension\Bolt\Members\Oauth2\Client\ProviderManager;
use Bolt\Extension\Bolt\Members\Oauth2\Handler\HandlerInterface;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Cookie;
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
     * @inheritDoc
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
            ->method('GET');

        $ctr
            ->before([$this, 'before'])
            ->after([$this, 'after'])
        ;

        return $ctr;
    }

    /**
     * Controller before render
     *
     * @param Request     $request
     * @param Application $app
     */
    public function before(Request $request, Application $app)
    {
        if ($request->get('_route') === 'authenticationLogin') {
            return;
        }

        /** @var ProviderManager $providerManager */
        $providerManager = $app['members.oauth.provider.manager'];
        $providerManager->setProvider($app, $request);
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
            $response->headers->setCookie(new Cookie(Session::COOKIE_AUTHORISATION, $cookie, 86400));
        }
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
        $data = [
            'csrf_protection' => true,
            'data'            => [
                'email' => $request->request->get('email')
            ],
        ];
        /** @var Form $form */
        $form = $app['form.factory']
            ->createBuilder(
                $app['members.forms']['type']['login'],
                $app['members.forms']['entity']['login'],
                $data
            )
            ->getForm()
        ;

        // Handle the form request data
        $form->handleRequest($request);
        if ($form->isValid()) {
            $account = $app['members.records']->getAccountByEmail($form->get('email')->getData());
            if (!$account instanceof Entity\Account) {
                $app['logger.flash']->info('Registration is required.');

                return new RedirectResponse($app['url_generator']->generate('registerProfile'));
            }

            $oauth = $app['members.records']->getOauthByGuid($account->getGuid());
            if (!$oauth instanceof Entity\Oauth) {
                $app['logger.flash']->info('Registration is required.');

                return new RedirectResponse($app['url_generator']->generate('registerProfile'));
            }

            if (!$oauth->getEnabled()) {
                $app['logger.flash']->info('Account disabled.');

                return new RedirectResponse($app['url_generator']->generate('authenticationLogin'));
            }

            if (password_verify($form->get('password')->getData(), $oauth->getPassword())) {
                return new RedirectResponse($request->headers->get('referer', '/'));
            }
        }

        $html = $app['render']->render($this->config->getTemplates('authentication', 'login'), [
            'form'       => $form->createView(),
            'twigparent' => $this->config->getTemplates('authentication', 'parent'),
        ]);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
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
        // Log a warning if this route is not HTTPS
        if (!$request->isSecure()) {
            $msg = sprintf("[Members][Controller]: Login route '%s' is not being served over HTTPS. This is insecure and vulnerable!", $request->getPathInfo());
            $app['logger.system']->critical($msg, ['event' => 'extensions']);
        }

        $this->setFinalRedirectUrl($app, $request);

        try {
            /** @var HandlerInterface $handler */
            $handler = $app['members.oauth.handler'];
            $response = $handler->login($request);
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }

        return $response;
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
        try {
            /** @var HandlerInterface $handler */
            $handler = $app['members.oauth.handler'];
            $response = $handler->process($request, 'authorization_code');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }

        if ($response instanceof RedirectResponse) {
            $response->setTargetUrl($this->getRedirectUrl($app));
        }

        $sessionResponse = $app['session']->get('redirect', null);
        if ($sessionResponse instanceof RedirectResponse) {
            $response = $sessionResponse;
            $app['session']->set('redirect', null);
        }

        // Flush any pending redirects
        $this->clearRedirectUrl($app);

        return $response;
    }

    /**
     * Save the redirect URL to the session.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return string
     */
    private function setFinalRedirectUrl(Application $app, Request $request)
    {
        if ($returnPage = $request->get('redirect')) {
            $returnPage = str_replace($app['resources']->getUrl('hosturl'), '', $returnPage);
        } else {
            $returnPage = $app['resources']->getUrl('hosturl');
        }

        $app['session']->set(self::FINAL_REDIRECT_KEY, $returnPage);

        return $returnPage;
    }

    /**
     * Get the saved redirect URL from the session.
     *
     * @param Application $app
     *
     * @return string
     */
    private function getRedirectUrl($app)
    {
        if ($returnPage = $app['session']->get(self::FINAL_REDIRECT_KEY)) {
            return $returnPage;
        }

        return $app['resources']->getUrl('hosturl');
    }

    /**
     * Clear the redirect URL.
     *
     * @param Application $app
     */
    private function clearRedirectUrl($app)
    {
        $app['session']->remove(self::FINAL_REDIRECT_KEY);
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
            $app['members.feedback']->set('message', 'An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } elseif ($e instanceof InvalidAuthorisationRequestException) {
            // Thrown deliberately internally
            $app['members.feedback']->set('message', 'An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } elseif ($e instanceof MissingAccountException) {
            // Thrown deliberately internally
            $app['members.feedback']->set('message', 'An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } else {
            // Yeah, this can't be good…
            $app['members.feedback']->set('message', 'A server error occurred, we are very sorry and someone has been notified!');
            $response = new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Dispatch an event so that subscribers can extend exception handling
        if ($app['dispatcher']->hasListeners(ExceptionEvent::ERROR)) {
            try {
                $app['dispatcher']->dispatch(ExceptionEvent::ERROR, new ExceptionEvent($e));
            } catch (\Exception $e) {
                $app['logger.system']->critical('[ClientLogin][Controller] Event dispatcher had an error', ['event' => 'exception', 'exception' => $e]);
            }
        }

        $app['members.feedback']->set('debug', $e->getMessage());
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
    public function displayExceptionPage(Application $app, \Exception $e)
    {
        $ext = $app['extensions']->get('Bolt/Members');
        $app['twig.loader.bolt_filesystem']->addPath($ext->getBaseDirectory()->getFullPath() . '/templates/error/');
        $context = [
            'parent'    => $app['members.config']->getTemplates('error', 'parent'),
            'feedback'  => $app['members.feedback']->get(),
            'exception' => $e,
        ];
        $html = $app['twig']->render($this->config->getTemplates('error', 'error'), $context);

        return new \Twig_Markup($html, 'UTF-8');
    }
}
