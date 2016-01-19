<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\Bolt\ClientLogin\Event\ClientLoginEvent;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\MenuTrait;
use Bolt\Extension\NutTrait;
use Bolt\Menu\MenuEntry;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Membership management extension for Bolt
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersExtension extends AbstractExtension implements ServiceProviderInterface, EventSubscriberInterface
{
    /** @var boolean */
    private $isAdmin;

    use ConfigTrait {
        getConfig as public;
    }
    use ControllerMountTrait;
    use NutTrait;
    use MenuTrait;

    /**
     * {@inheritdoc}
     */
    final public function register(Application $app)
    {
        $this->extendTwigService();
        $this->extendNutService();
        $this->extendMenuService();
    }

    public function boot(Application $app)
    {
        $this->container = $app;
        $this->container['dispatcher']->addSubscriber($this);
        $this->subscribe($this->container['dispatcher']);

        // Check & create database tables if required
        $records = new Records($app, $this->getConfig());
        $records->dbCheck();
    }

    /**
     * Hook for ClientLogin login events
     *
     * @param ClientLoginEvent $event
     */
    public function loginCallback(ClientLoginEvent $event)
    {
        $auth = new Authenticate($this->getContainer(), $this->getConfig());
        $auth->login($event);
    }

    /**
     * Hook for ClientLogin logout events
     *
     * @param ClientLoginEvent $event
     */
    public function logoutCallback(ClientLoginEvent $event)
    {
        $auth = new Authenticate($this->getContainer(), $this->getConfig());
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
            '/members' => new Controller\MembersAdminController($this->getContainer(), $this->getConfig()),
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
