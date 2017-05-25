<?php

namespace Bolt\Extension\BoltAuth\Auth\Controller;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Extension\BoltAuth\Auth\Form;
use Bolt\Extension\BoltAuth\Auth\AuthExtension;
use Bolt\Extension\BoltAuth\Auth\Pager\Pager;
use Bolt\Extension\BoltAuth\Auth\Pager\PagerEntity;
use Bolt\Extension\BoltAuth\Auth\Storage;
use Bolt\Version;
use Carbon\Carbon;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Backend controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Backend extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = parent::connect($app);
        $ctr->value(Zone::KEY, Zone::BACKEND);

        $authBaseUrl = Version::compare('3.2.999', '<')
            ? '/extensions/auth'
            : '/extend/auth'
        ;

        $ctr->match($authBaseUrl, [$this, 'admin'])
            ->bind('authAdmin')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match($authBaseUrl . '/add', [$this, 'userAdd'])
            ->bind('authAdminUserAdd')
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match($authBaseUrl . '/action/userDelete', [$this, 'userDelete'])
            ->bind('authAdminUserDel')
            ->method(Request::METHOD_POST)
        ;

        $ctr->match($authBaseUrl . '/action/userEnable', [$this, 'userEnable'])
            ->bind('authAdminUserEnable')
            ->method(Request::METHOD_POST)
        ;

        $ctr->match($authBaseUrl . '/action/userDisable', [$this, 'userDisable'])
            ->bind('authAdminUserDisable')
            ->method(Request::METHOD_POST)
        ;

        $ctr->match($authBaseUrl . '/action/roleAdd', [$this, 'roleAdd'])
            ->bind('authAdminUserRoleAdd')
            ->method(Request::METHOD_POST)
        ;

        $ctr->match($authBaseUrl . '/action/roleDel', [$this, 'roleDel'])
            ->bind('authAdminUserRoleDel')
            ->method(Request::METHOD_POST)
        ;

        $ctr->match($authBaseUrl . '/edit/{guid}', [$this, 'userEdit'])
            ->bind('authAdminUserEdit')
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->before([$this, 'before']);

        return $ctr;
    }

    /**
     * Controller before render
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return RedirectResponse|null
     */
    public function before(Request $request, Application $app)
    {
        $user = $app['users']->getCurrentUser();
        $userid = $user['id'];

        foreach ($this->getAuthConfig()->getRolesAdmin() as $role) {
            if ($app['users']->hasRole($userid, $role)) {
                $this->addWebAssets($app);

                return null;
            }
        }

        /** @var UrlGeneratorInterface $generator */
        $generator = $app['url_generator'];

        return new RedirectResponse($generator->generate('dashboard'), Response::HTTP_SEE_OTHER);
    }

    /**
     * Inject web assets for our route.
     *
     * @param Application $app
     */
    private function addWebAssets(Application $app)
    {
        /** @var AuthExtension $extension */
        $extension = $app['extensions']->get('Bolt/Auth');
        $dir = '/' . $extension->getWebDirectory()->getPath();
        $assets = [
            (new Stylesheet($dir . '/css/sweetalert.css'))->setZone(Zone::BACKEND)->setLate(false),
            (new JavaScript($dir . '/js/sweetalert.min.js'))->setZone(Zone::BACKEND)->setPriority(10)->setLate(true),
            (new Stylesheet($dir . '/css/auth-admin.css'))->setZone(Zone::BACKEND)->setLate(false),
            (new JavaScript($dir . '/js/auth-admin.js'))->setZone(Zone::BACKEND)->setPriority(20)->setLate(true),
        ];

        foreach ($assets as $asset) {
            $app['asset.queue.file']->add($asset);
        }
    }

    /**
     * The main admin page
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function admin(Application $app, Request $request)
    {
        /** @var Storage\Repository\Account $repo */
        $repo = $app['auth.repositories']['account'];
        $repo->setPagerEnabled(true);
        $queries = [
            'orderBy' => $request->query->get('orderby', 'displayname'),
            'order'   => $request->query->get('order'),
            'search'  => $request->query->get('search'),
        ];

        try {
            /** @var Pager $auth */
            if ($queries['search'] === null) {
                $auth = $app['auth.records']->getAccounts($queries['orderBy'], $queries['order']);
            } else {
                $auth = $app['auth.records']->searchAccount($queries['search'], $queries['orderBy'], $queries['order']);
            }

            $auth
                ->setMaxPerPage(10)
                ->setCurrentPage($request->query->getInt('page', 1))
                ->getCurrentPageResults()
            ;
        } catch (\Exception $e) {
            $this->handleException($app, $e);
            $auth = [];
        }

        try {
            $roles = $app['auth.roles']->getRoles();
        } catch (\Exception $e) {
            $this->handleException($app, $e);
            $roles = [];
        }

        $pager = new PagerEntity();
        if (!empty($auth)) {
            $pager
                ->setFor('Auths')
                ->setCurrent($auth->getCurrentPage())
                ->setCount($auth->getNbResults())
                ->setTotalPages($auth->getNbPages())
                ->setShowingFrom($auth->getCurrentPageOffsetStart())
                ->setShowingTo($auth->getCurrentPageOffsetEnd())
            ;
        }

        $html = $app['twig']->render('@AuthAdmin/auth.twig', [
            'auth' => $auth,
            'roles'   => $roles,
            'queries' => $queries,
            'pager'   => [
                'pager' => $pager,
                'surr'  => 4,
            ],
            'context' => [
                'contenttype' => [
                    'slug' => 'pager',
                ],
            ],
        ]);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * @param Application $app
     * @param \Exception  $e
     *
     * @throws \Exception
     */
    protected function handleException(Application $app, \Exception $e)
    {
        if ($e instanceof TableNotFoundException) {
            $msg = sprintf('Auth database tables have not been created! Please <a href="%s">update your database</a>.', $app['url_generator']->generate('dbcheck'));
            $app['logger.flash']->error($msg);
        } elseif ($e instanceof OutOfRangeCurrentPageException) {
            $app['logger.flash']->error('Page does not exist');
        } else {
            throw $e;
        }
    }

    /**
     * Add a auth.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function userAdd(Application $app, Request $request)
    {
        $builder = $app['auth.forms.manager']->getFormProfileEdit($request, true, null);
        $form = $builder->getForm(Form\AuthForms::PROFILE_EDIT);

        // Handle the form request data
        if ($form->isValid()) {
            /** @var Form\Entity\Profile $entity */
            $entity = $builder->getEntity(Form\AuthForms::PROFILE_EDIT);

            // Create and store the account entity
            $account = new Storage\Entity\Account();
            $account->setGuid($entity->getGuid());
            $account->setDisplayname($entity->getDisplayname());
            $account->setEmail($entity->getEmail());
            $account->setRoles([]);
            $account->setEnabled(true);
            $app['auth.records']->saveAccount($account);

            // Save the password to a meta record
            $oauth = new Storage\Entity\Oauth();
            $oauth->setGuid($account->getGuid());
            $oauth->setResourceOwnerId($account->getGuid());
            $oauth->setEnabled(true);
            $app['auth.records']->saveOauth($oauth);

            // Create a local provider entry
            $provider = new Storage\Entity\Provider();
            $provider->setGuid($account->getGuid());
            $provider->setProvider('local');
            $provider->setResourceOwnerId($account->getGuid());
            $provider->setLastupdate(Carbon::now());
            $app['auth.records']->saveProvider($provider);

            return new RedirectResponse($app['url_generator']->generate('authAdmin'));
        }

        $html = $app['auth.forms.manager']->renderForms($builder, $app['twig'], '@AuthAdmin/profile_add.twig');

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Delete a auth.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function userDelete(Application $app, Request $request)
    {
        foreach ($request->request->get('auth') as $guid) {
            try {
                $app['auth.admin']->deleteAccount($guid);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('userDelete', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('userDelete'));
    }

    /**
     * Edit a auth.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $guid
     *
     * @return RedirectResponse|Response
     */
    public function userEdit(Application $app, Request $request, $guid)
    {
        /** @var Storage\Records $records */
        $records = $app['auth.records'];

        if (!Uuid::isValid($guid)) {
            $app['logger.flash']->error(sprintf('Auth GUID %s is not valid', $guid));

            new RedirectResponse($app['url_generator']->generate('authAdmin'));
        }

        if ($records->getAccountByGuid($guid) === false) {
            $app['logger.flash']->error(sprintf('Auth GUID %s does not exist', $guid));

            new RedirectResponse($app['url_generator']->generate('authAdmin'));
        }

        $builder = $app['auth.forms.manager']->getFormProfileEdit($request, true, $guid);
        $form = $builder->getForm(Form\AuthForms::PROFILE_EDIT);

        // Handle the form request data
        if ($form->isValid()) {

            /** @var Form\Entity\Profile $entity */
            $entity = $builder->getEntity(Form\AuthForms::PROFILE_EDIT);
            $account = $records->getAccountByGuid($entity->getGuid());
            if ($account === false) {
                throw new \RuntimeException(sprintf('Unable to find account for %s', $entity->getGuid()));
            }
            $app['auth.records.profile']->saveProfileForm($account, $form);

            $app['auth.oauth.provider.manager']->setProvider($app, 'local');
            $profileUrl = $app['url_generator']->generate('authAdmin');

            return new RedirectResponse($profileUrl);
        }

        $html = $app['auth.forms.manager']->renderForms($builder, $app['twig'], '@AuthAdmin/profile_edit.twig', ['guid' => $guid]);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Enable a auth.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function userEnable(Application $app, Request $request)
    {
        foreach ($request->request->get('auth') as $guid) {
            try {
                $app['auth.admin']->enableAccount($guid);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('userEnable', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('userEnable'));
    }

    /**
     * Disable a auth.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function userDisable(Application $app, Request $request)
    {
        foreach ($request->request->get('auth') as $guid) {
            try {
                $app['auth.admin']->disableAccount($guid);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('userEnable', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('userDisable'));
    }

    /**
     * Add role(s) for auth(s).
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function roleAdd(Application $app, Request $request)
    {
        $role = $request->request->get('role');
        if ($role === null) {
            return new JsonResponse($this->getResult('roleAdd', new \RuntimeException('Role was empty!')), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        foreach ($request->request->get('auth') as $guid) {
            try {
                $app['auth.admin']->addAccountRole($guid, $role);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('roleAdd', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('roleAdd'));
    }

    /**
     * Delete role(s) from auth(s).
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function roleDel(Application $app, Request $request)
    {
        $role = $request->request->get('role');
        if ($role === null) {
            return new JsonResponse($this->getResult('roleAdd', new \RuntimeException('Role was empty!')), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        foreach ($request->request->get('auth') as $guid) {
            try {
                $app['auth.admin']->deleteAccountRole($guid, $role);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('roleDel', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('roleDel'));
    }

    /**
     * Get an array result suitable to return to the AJAX caller.
     *
     * @param string     $task
     * @param \Exception $e
     *
     * @return array
     */
    protected function getResult($task, \Exception $e = null)
    {
        if ($e === null) {
            return [
                'job'    => $task,
                'result' => true,
                'data'   => '',
            ];
        }

        return [
            'job'    => $task,
            'result' => false,
            'data'   => $e->getMessage(),
        ];
    }
}
