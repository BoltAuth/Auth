<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\MembersExtension;
use Bolt\Extension\Bolt\Members\Storage;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Backend controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Backend implements ControllerProviderInterface
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
        $ctr->value(Zone::KEY, Zone::BACKEND);

        $ctr->match('/extend/members', [$this, 'admin'])
            ->bind('membersAdmin')
            ->method('GET')
        ;

        $ctr->match('/extend/members/add', [$this, 'userAdd'])
            ->bind('membersAdminUserAdd')
            ->method('GET|POST')
        ;

        $ctr->match('/extend/members/action/userDelete', [$this, 'userDelete'])
            ->bind('membersAdminUserDel')
            ->method('POST')
        ;

        $ctr->match('/extend/members/action/userEnable', [$this, 'userEnable'])
            ->bind('membersAdminUserEnable')
            ->method('POST')
        ;

        $ctr->match('/extend/members/action/userDisable', [$this, 'userDisable'])
            ->bind('membersAdminUserDisable')
            ->method('POST')
        ;

        $ctr->match('/extend/members/action/roleAdd', [$this, 'roleAdd'])
            ->bind('membersAdminUserRoleAdd')
            ->method('POST')
        ;

        $ctr->match('/extend/members/action/roleDel', [$this, 'roleDel'])
            ->bind('membersAdminUserRoleDel')
            ->method('POST')
        ;

        $ctr->match('/extend/members/edit/{guid}', [$this, 'userEdit'])
            ->assert('guid', '^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-4[0-9A-Fa-f]{3}-[89ABab][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}$')
            ->bind('membersAdminUserEdit')
            ->method('GET|POST')
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
        $user    = $app['users']->getCurrentUser();
        $userid  = $user['id'];

        foreach ($this->config->getRolesAdmin() as $role) {
            if ($app['users']->hasRole($userid, $role)) {
                if (!$request->isXmlHttpRequest()) {
                    $this->addWebAssets($app);
                }

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
        /** @var MembersExtension $extension */
        $extension = $app['extensions']->get('Bolt/Members');
        $dir = $extension->getRelativeUrl();
        $saCss = (new Stylesheet($dir . 'css/sweetalert.css'))->setZone(Zone::BACKEND)->setLate(false);
        $saJs = (new JavaScript($dir . 'js/sweetalert.min.js'))->setZone(Zone::BACKEND)->setPriority(10)->setLate(true);
        $mCss = (new Stylesheet($dir . 'css/members-admin.css'))->setZone(Zone::BACKEND)->setLate(false);
        $mJs = (new JavaScript($dir . 'js/members-admin.js'))->setZone(Zone::BACKEND)->setPriority(20)->setLate(true);

        $app['asset.queue.file']->add($saCss);
        $app['asset.queue.file']->add($saJs);
        $app['asset.queue.file']->add($mCss);
        $app['asset.queue.file']->add($mJs);
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
        $this->addTwigPath($app);

        $html = $app['twig']->render('@MembersAdmin/members.twig', [
            'members' => $app['members.records']->getAccounts(),
            'roles'   => $app['members.roles']->getRoles(),
        ]);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Add a member.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function userAdd(Application $app, Request $request)
    {
        $app['members.form.components']['type']['profile_edit']->setRequirePassword(true);
        $app['members.form.profile_edit']->setGuid($guid = Uuid::uuid4()->toString());
        $resolvedForm = $app['members.forms.manager']->getFormProfileEdit($request, true, $guid);
        $form = $resolvedForm->getForm('form_profile');

        // Handle the form request data
        if ($form->isValid()) {
            // Create and store the account entity
            $account = new Storage\Entity\Account();
            $account->setDisplayname($form->get('displayname')->getData());
            $account->setEmail($form->get('email')->getData());
            $account->setRoles([]);
            $account->setEnabled(true);
            $app['members.records']->saveAccount($account);

            // Save the password to a meta record
            $oauth = new Storage\Entity\Oauth();
            $oauth->setGuid($account->getGuid());
            $oauth->setResourceOwnerId($account->getGuid());
            $oauth->setEnabled(true);
            $app['members.records']->saveOauth($oauth);

            // Create a local provider entry
            $provider = new Storage\Entity\Provider();
            $provider->setGuid($account->getGuid());
            $provider->setProvider('local');
            $provider->setResourceOwnerId($account->getGuid());
            $provider->setLastupdate(Carbon::now());
            $app['members.records']->saveProvider($provider);

            $app['members.form.profile_edit']
                ->setAccount($account)
                ->setGuid($account->getGuid())
                ->saveForm($app['members.records'], $app['dispatcher'])
            ;

            return new RedirectResponse($app['url_generator']->generate('membersAdmin'));
        }
        $this->addTwigPath($app);
        $html = $app['members.forms.manager']->renderForms($resolvedForm, '@MembersAdmin/profile_add.twig');

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Delete a member.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function userDelete(Application $app, Request $request)
    {
        foreach ($request->request->get('members') as $guid) {
            try {
                $app['members.admin']->deleteAccount($guid);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('userDelete', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('userDelete'));
    }

    public function userEdit(Application $app, Request $request, $guid)
    {
        if ($app['members.records']->getAccountByGuid($guid) === false) {
            $app['logger.flash']->error(sprintf('Member GUID %s does not exist', $guid));

            new RedirectResponse($app['url_generator']->generate('membersAdmin'));
        }

        $app['members.form.components']['type']['profile_edit']->setRequirePassword(false);
        $app['members.form.profile_edit']->setGuid($guid);
        $resolvedForm = $app['members.forms.manager']->getFormProfileEdit($request, true, $guid);
        $form = $resolvedForm->getForm('form_profile');

        // Handle the form request data
        if ($form->isValid()) {
            $app['members.oauth.provider.manager']->setProvider($app, 'local');
            $app['members.form.profile_edit']->saveForm($app['members.records'], $app['dispatcher']);
            // Redirect to our profile page.
            $response =  new RedirectResponse($app['url_generator']->generate('membersAdmin'));

            return $response;
        }
        $this->addTwigPath($app);
        $html = $app['members.forms.manager']->renderForms($resolvedForm, '@MembersAdmin/profile_edit.twig', ['guid' => $guid]);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Enable a member.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function userEnable(Application $app, Request $request)
    {
        foreach ($request->request->get('members') as $guid) {
            try {
                $app['members.admin']->enableAccount($guid);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('userEnable', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('userEnable'));
    }

    /**
     * Disable a member.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function userDisable(Application $app, Request $request)
    {
        foreach ($request->request->get('members') as $guid) {
            try {
                $app['members.admin']->disableAccount($guid);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('userEnable', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('userDisable'));
    }

    /**
     * Add role(s) for member(s).
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

        foreach ($request->request->get('members') as $guid) {
            try {
                $app['members.admin']->addAccountRole($guid, $role);
            } catch (\Exception $e) {
                return new JsonResponse($this->getResult('roleAdd', $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse($this->getResult('roleAdd'));
    }

    /**
     * Delete role(s) from member(s).
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

        foreach ($request->request->get('members') as $guid) {
            try {
                $app['members.admin']->deleteAccountRole($guid, $role);
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
    private function getResult($task, \Exception $e = null)
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
            'result' => true,
            'data'   => $e->getMessage(),
        ];
    }

    /**
     * Set our Twig template path
     *
     * @param Application $app
     */
    private function addTwigPath(Application $app)
    {
        /** @var MembersExtension $extension */
        $extension = $app['extensions']->get('Bolt/Members');
        $dir = sprintf('extensions://%s/templates/admin', $extension->getBaseDirectory()->getFullPath());

        $app['twig.loader.bolt_filesystem']->addPath($dir, 'MembersAdmin');
    }
}
