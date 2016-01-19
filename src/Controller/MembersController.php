<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\ClientLogin\Client;
use Bolt\Extension\Bolt\ClientLogin\Session;
use Bolt\Extension\Bolt\Members\Authenticate;
use Bolt\Extension\Bolt\Members\Entity\Profile;
use Bolt\Extension\Bolt\Members\Entity\Register;
use Bolt\Extension\Bolt\Members\Extension;
use Bolt\Extension\Bolt\Members\Form\ProfileType;
use Bolt\Extension\Bolt\Members\Form\RegisterType;
use Bolt\Extension\Bolt\Members\Members;
use Bolt\Extension\Bolt\Members\Records;
use Silex;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Members extension controller
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersController implements ControllerProviderInterface
{
    /** @var array */
    private $config;
    /** @var Members */
    private $members;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Silex\Application $app
     *
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        /**
         * @var $ctr \Silex\ControllerCollection
         */
        $ctr = $app['controllers_factory'];

        // New member
        $ctr->match('/register', [$this, 'register'])
            ->method('GET|POST');

        // My profile
        $ctr->match('/profile', [$this, 'profileedit'])
            ->method('GET|POST');

        // Member profile
        $ctr->match('/profile/{id}', [$this, 'profileview'])
            ->assert('id', '\d*')
            ->method('GET');

        return $ctr;
    }

    /**
     * @param \Silex\Application                        $app
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Twig_Markup
     */
    public function register(Application $app, Request $request)
    {
        // Ensure we have a valid Client Login session
        if (!$app['clientlogin.session']->isLoggedIn()) {
            return new Response('No valid Client Login session!', Response::HTTP_FORBIDDEN, ['content-type' => 'text/html']);
        }

        // Get redirect that is set for ClientLogin
        $redirect = $app['clientlogin.session.handler']->get('pending');

        // Get session data we need from ClientLogin
        $clientlogin = $app['clientlogin.session.handler']->get('clientlogin');

        // If there is no ClientLogin data in the session, they shouldn't be here
        if (empty($clientlogin)) {
            return new Response('Invalid session referral!', Response::HTTP_FORBIDDEN, ['content-type' => 'text/html']);
        }

        // Get a Client object
        $userdata = $app['clientlogin.db']->getUserProfileByID($clientlogin);

        // Create new register form
        $register = new Register();
        $data = [
            'csrf_protection' => $this->config['csrf'],
            'data'            => [
                'username'    => substr($app['slugify']->slugify($userdata->name), 0, 32),
                'displayname' => $userdata->name,
                'email'       => $userdata->email,
            ],
        ];

        $form = $app['form.factory']->createBuilder(new RegisterType(), $register, $data)
                                    ->getForm();

        // Handle the form request data
        $form->handleRequest($request);

        // If we're in a POST, validate the form
        if ($request->getMethod() === 'POST') {
            if ($form->isValid()) {
                // Create new Member record and go back to where we came from
                $auth = new Authenticate($app);
                if ($auth->addMember($request->get('register'), $userdata)) {
                    // Clear any redirect that ClientLogin has pending
                    $app['clientlogin.session.handler']->remove('pending');
                    $app['clientlogin.session.handler']->remove('clientlogin');

                    // Redirect
                    if (empty($redirect)) {
                        return new RedirectResponse($app['resources']->getUrl('hosturl'));
                    } else {
                        $returnpage = str_replace($app['resources']->getUrl('hosturl'), '', $redirect);

                        return new RedirectResponse($returnpage);
                    }
                } else {
                    // Something is wrong here
                }
            }
        }

        // Add assets to Twig path
        $this->addTwigPath($app);

        $html = $app['render']->render($this->config['templates']['register'], [
            'form'       => $form->createView(),
            'twigparent' => $this->config['templates']['parent'],
        ]);

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * @param \Silex\Application                        $app
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Twig_Markup
     */
    public function profileedit(Application $app, Request $request)
    {
        $member = $app['members']->isAuth();

        if (!$member) {
            return new Response('Invalid profile session!', Response::HTTP_FORBIDDEN, ['content-type' => 'text/html']);
        }

        $member = $app['members']->getMember('id', $member);
        $id = $member['id'];

        // Create new register form
        $profile = new Profile();
        $data = [
            'csrf_protection' => $this->config['csrf'],
            'data'            => [
                'username'    => $member['username'],
                'displayname' => $member['displayname'],
                'email'       => $member['email'],
                'readonly'    => false,
            ],
        ];

        $form = $app['form.factory']->createBuilder(new ProfileType(), $profile, $data)
                                    ->getForm();

        // Handle the form request data
        $form->handleRequest($request);

        // If we're in a POST, validate the form
        if ($request->getMethod() === 'POST') {
            if ($form->isValid()) {
                $reponse = $request->get('profile');

                $records = new Records($app);
                $records->updateMember($id, [
                    'displayname' => $reponse['displayname'],
                    'email'       => $reponse['email'],
                ]);

                return new RedirectResponse($app['resources']->getUrl('hosturl'));
            }
        }

        // Add assets to Twig path
        $this->addTwigPath($app);

        $html = $app['render']->render(
            $this->config['templates']['profile_edit'], [
                'form'       => $form->createView(),
                'twigparent' => $this->config['templates']['parent'],
        ]);

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * @param \Silex\Application                        $app
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Twig_Markup
     */
    public function profileview(Application $app, Request $request, $id)
    {
        $member = $app['members']->getMember('id', $id);

        if (! $member) {
            return new Response('Invalid profile!', Response::HTTP_FORBIDDEN, ['content-type' => 'text/html']);
        } else {
            $member['avatar'] = $app['members']->getMemberMeta($id, 'avatar');
        }

        // Add assets to Twig path
        $this->addTwigPath($app);

        $html = $app['render']->render(
            $this->config['templates']['profile_view'], [
                'displayname' => $member['displayname'],
                'email'       => $member['email'],
                'avatar'      => $member['avatar']['value'],
                'twigparent'  => $this->config['templates']['parent'],
        ]);

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * @param Silex\Application $app
     */
    private function addTwigPath(Silex\Application $app)
    {
        $app['twig.loader.filesystem']->addPath(dirname(dirname(__DIR__)) . '/assets');
    }
}
