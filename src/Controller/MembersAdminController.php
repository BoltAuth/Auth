<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\Admin;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Members admin area controller
 *
 * Copyright (C) 2014  Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class MembersAdminController implements ControllerProviderInterface
{
    /** @var Application */
    private $app;
    /** @var array */
    private $config;
    /** @var Admin */
    private $admin;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * @param \Silex\Application $app
     *
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        /** @var $ctr \Silex\ControllerCollection */
        $ctr = $app['controllers_factory'];

        // Admin page
        $ctr->match('/', [$this, 'admin'])
            ->bind('MembersAdmin')
            ->before([$this, 'before'])
            ->method('GET');

        // AJAX requests
        $ctr->match('/ajax', [$this, 'ajax'])
            ->method('GET|POST');

        return $ctr;
    }

    /**
     * Controller before render
     *
     * @param Request            $request
     * @param \Silex\Application $app
     *
     * @return null|RedirectResponse
     */
    public function before(Request $request, Application $app)
    {

        // Enable HTML snippets in our routes so that JS & CSS gets inserted
        //$app['htmlsnippets'] = true;

        //// Add our JS & CSS
        //$app[Extension::CONTAINER]->addJavascript('public/js/members.admin.js', true);
        //
        //// Temporary until I fork to it's own extension
        //$app[Extension::CONTAINER]->addCss('public/css/sweet-alert.css', true);
        //$app[Extension::CONTAINER]->addJavascript('public/js/sweet-alert.min.js', true);

        $user    = $app['users']->getCurrentUser();
        $userid  = $user['id'];

        foreach ($this->config['admin_roles'] as $role) {
            if ($app['users']->hasRole($userid, $role)) {
                return null;
            }
        }

        /** @var UrlGeneratorInterface $generator */
        $generator = $this->app['url_generator'];

        return new RedirectResponse($generator->generate('dashboard'), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * The main admin page
     *
     * @param \Silex\Application $app
     * @param Request            $request
     *
     * @return \Twig_Markup
     */
    public function admin(Application $app, Request $request)
    {
        $this->addTwigPath($app);

        $html = $app['render']->render('members.twig', [
            'members' => $this->getAdmin()->getMembers(),
            'roles'   => $app['members']->getAvailableRoles(),
        ]);

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * Members Admin AJAX controller
     *
     * @param \Silex\Application $app
     * @param Request            $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ajax(Application $app, Request $request)
    {
        // Get the task name
        $task = $app['request']->get('task');
        if ($task === null) {
            // Yeah, nah
            return new Response('Invalid request parameters', Response::HTTP_BAD_REQUEST);
        }

        if (!$request->isMethod('POST')) {
            // Yeah, nah
            return new Response('Invalid request parameters', Response::HTTP_BAD_REQUEST);
        }
        if ($task === 'userAdd') {
            /*
             * Add a user
             */
            try {
                //$app['members']->
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult($task, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse($this->getResult($task));
        } elseif ($task === 'userDel') {
            /*
             * Delete a user
             */
            try {
                //
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult($task, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse($this->getResult($task));
        } elseif ($task === 'userEnable') {
            /*
             * Enable a user
             */
            try {
                foreach ($request->request->get('members') as $id) {
                    $this->getAdmin()->memberEnable($id);
                }
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult($task, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse($this->getResult($task));
        } elseif ($task === 'userDisable') {
            /*
             * Disable a user
             */
            try {
                foreach ($request->request->get('members') as $id) {
                    $this->getAdmin()->memberDisable($id);
                }
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult($task, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse($this->getResult($task));
        } elseif ($task === 'roleAdd') {
            /*
             * Add a role to user(s)
             */
            try {
                foreach ($request->request->get('members') as $id) {
                    $this->getAdmin()->memberRolesAdd($id, $request->request->get('role'));
                }
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult($task, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse($this->getResult($task));
        } elseif ($task === 'roleDel') {
            /*
             * Delete a role from user(s)
             */
            try {
                foreach ($request->request->get('members') as $id) {
                    $this->getAdmin()->memberRolesRemove($id, $request->request->get('role'));
                }
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult($task, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse($this->getResult($task));
        }
    }

    /**
     * @param string     $task
     * @param \Exception $e
     *
     * @return array
     */
    private function getResult($task, \Exception $e = null)
    {
        if (is_null($e)) {
            return [
                'job'    => $task,
                'result' => true,
                'data'   => '',
            ];
        }

        return [
            'job'    => $task,
            'result' => true,
            'data'   => $e->getMessage(),
        ];
    }

    /**
     * Set our Twig template path
     *
     * @param \Silex\Application $app
     */
    private function addTwigPath(Application $app)
    {
        $app['twig.loader.filesystem']->addPath(dirname(dirname(__DIR__)) . '/templates/admin');
    }

    /**
     * @return Admin
     */
    private function getAdmin()
    {
        if ($this->admin === null) {
            $this->admin = new Admin($this->app, $this->config);
        }

        return $this->admin;
    }
}
