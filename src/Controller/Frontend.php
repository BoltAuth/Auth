<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Carbon\Carbon;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\Form;
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
     * @inheritDoc
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
     * Edit an existing member profile.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function editProfile(Application $app, Request $request)
    {
        $memberSession = $app['members.session']->getAuthorisation();

        if ($memberSession === null) {
            return new Response('TODO: No session found, please login', Response::HTTP_FORBIDDEN);
        }

        // Get the stored account & meta
        $account = $app['members.records']->getAccountByGuid($memberSession->getGuid());
        $meta = $app['members.records']->getAccountMetaAll($memberSession->getGuid());

        // Add account fields and meta fields to the form data
        $fields = [
            'displayname' => $account->getDisplayname(),
            'email' => $account->getEmail(),
        ];
        /** @var Entity\AccountMeta $metaEntity */
        foreach ((array) $meta as $metaEntity) {
            $fields[$metaEntity->getMeta()] = $metaEntity->getValue();
        }
        $data = [
            'csrf_protection' => true,
            'data'            => $fields,
        ];
        /** @var Form $form */
        $form = $app['form.factory']
            ->createBuilder(
                $app['members.forms']['type']['profile'],
                $app['members.forms']['entity']['profile'],
                $data
            )
            ->getForm()
        ;

        // Handle the form request data
        $form->handleRequest($request);
        if ($form->isValid()) {
            $account->setDisplayname($form->get('displayname')->getData());
            $account->setEmail($form->get('email')->getData());
            $app['members.records']->saveAccount($account);

            //if ($redirect = $app['session']->get('members_redirect')) {
            //    $response = new RedirectResponse($redirect);
            //} else {
            //    $response = new RedirectResponse($app['resources']->getUrl('hosturl'));
            //}
        }

        $html = $app['render']->render($this->config->getTemplates('profile', 'edit'), [
            'form'       => $form->createView(),
            'twigparent' => $this->config->getTemplates('profile', 'parent'),
        ]);

        $response = new Response(new \Twig_Markup($html, 'UTF-8'));

        return $response;
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
        $data = [
            'csrf_protection' => true,
            'data'            => [],
        ];
        /** @var Form $form */
        $form = $app['form.factory']
            ->createBuilder(
                $app['members.forms']['type']['register'],
                $app['members.forms']['entity']['register'],
                $data
            )
            ->getForm()
        ;

        // Handle the form request data
        $form->handleRequest($request);
        if ($form->isValid()) {
            // Create and store the account entity
            $account = new Entity\Account();
            $account->setDisplayname($form->get('displayname')->getData());
            $account->setEmail($form->get('email')->getData());
            $account->setRoles($app['members.config']->getRolesRegister());
            $account->setEnabled(true);
            $account->setLastseen(Carbon::now());
            $account->setLastip($app['request_stack']->getCurrentRequest()->getClientIp());
            $app['members.records']->saveAccount($account);

            // Set up the initial session.
            $localProvider = new Provider\Local();
            $localAccessToken = $localProvider->getAccessToken('password', []);
            $app['members.session']->createAuthorisation($account->getGuid(), 'Local', $localAccessToken);

            // Create a local provider entry
            $provider = new Entity\Provider();
            $provider->setGuid($account->getGuid());
            $provider->setProvider('Local');
            $provider->setResourceOwnerId($account->getGuid());
            $provider->setLastupdate(Carbon::now());
            $app['members.records']->saveProvider($provider);

            // Redirect to our profile page.
            $response =  new RedirectResponse($app['url_generator']->generate('membersProfileEdit'));

            return $response;
        }

        $html = $app['render']->render($this->config->getTemplates('profile', 'register'), [
            'form'       => $form->createView(),
            'twigparent' => $this->config->getTemplates('profile', 'parent'),
        ]);

        $response = new Response(new \Twig_Markup($html, 'UTF-8'));

        return $response;
    }
}
