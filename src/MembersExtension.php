<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Controller\Zone;
use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\Bolt\ClientLogin\Event\ClientLoginEvent;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\NutTrait;
use Bolt\Menu\MenuEntry;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Membership management extension for Bolt
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
class MembersExtension extends AbstractExtension implements ServiceProviderInterface, EventSubscriberInterface
{
    /** @var boolean */
    private $isAdmin;

    use ConfigTrait;
    use ControllerMountTrait;
    use NutTrait;

    /**
     * {@inheritdoc}
     */
    final public function register(Application $app)
    {
        $this->extendTwigService();
        $this->extendNutService();
    }

    public function boot(Application $app)
    {
        $app->before([$this, 'before']);
    }

    /**
     * @param Request     $request
     * @param Application $app
     */
    public function before(Request $request, Application $app)
    {
        if (Zone::isBackend($request)) {
            // Check & create database tables if required
            $records = new Records($app);
            $records->dbCheck();
        }
    }

    /**
     * Hook for ClientLogin login events
     *
     * @param ClientLoginEvent $event
     */
    public function loginCallback(ClientLoginEvent $event)
    {
        $auth = new Authenticate($this->app);
        $auth->login($event);
    }

    /**
     * Hook for ClientLogin logout events
     *
     * @param ClientLoginEvent $event
     */
    public function logoutCallback(ClientLoginEvent $event)
    {
        $auth = new Authenticate($this->app);
        $auth->logout($event);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ControllerEvents::MOUNT => [
                ['onMountControllers', 0],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerMenuEntries()
    {
        $config = $this->getConfig();

        return [
            (new MenuEntry('members', 'members'))
                ->setLabel(Trans::__('Members'))
                ->setIcon('fa:users')
                ->setPermission(implode('||', $config['admin_roles'])),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new Provider\MembersServiceProvider($this->getContainer()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendControllers()
    {
        return [
            '/members' => new Controller\MembersAdminController($this->getConfig()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $config = $this->getConfig();
        $base = '/' . ltrim($config['basepath'], '/');

        return [
            $base => new Controller\MembersController($this->getConfig()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener('clientlogin.Login',  [$this, 'loginCallback']);
        $dispatcher->addListener('clientlogin.Logout', [$this, 'logoutCallback']);
    }

    /**
     * Register Twig functions.
     */
    private function extendTwigService()
    {
        /** @var Application $app */
        $app = $this->getContainer();
        $config = $this->getConfig();

        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig) use ($app, $config) {
                    $twig->addExtension(new Twig\MembersExtension($app, $config));

                    return $twig;
                }
            )
        );

        $app['safe_twig'] = $app->share(
            $app->extend(
                'safe_twig',
                function (\Twig_Environment $twig) use ($app, $config) {
                    $twig->addExtension(new Twig\MembersExtension($app, $config));

                    return $twig;
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'basepath'     => 'members',
            'templates'    => [
                'parent'        => 'members.twig',
                'register'      => 'members_register.twig',
                'profile_edit'  => 'members_profile_edit.twig',
                'profile_view'  => 'members_profile_view.twig',
            ],
            'registration' => true,
            'csrf'         => true,
            'admin_roles'  => ['root', 'admin', 'developer', 'chief-editor'],
        ];
    }
}
