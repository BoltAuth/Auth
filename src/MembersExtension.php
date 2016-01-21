<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\Bolt\ClientLogin\Event\ClientLoginEvent;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\MenuTrait;
use Bolt\Extension\NutTrait;
use Bolt\Extension\TwigTrait;
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
    use TwigTrait;

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
        $app['dispatcher']->addSubscriber($this);

        // Check & create database tables if required
        $app['members.records']->dbCheck();

        $this->container = $app;
    }

    /**
     * Hook for ClientLogin login events
     *
     * @param ClientLoginEvent $event
     */
    public function loginCallback(ClientLoginEvent $event)
    {
        $app = $this->getContainer();
        $app['members.authenticate']->login($event);
    }

    /**
     * Hook for ClientLogin logout events
     *
     * @param ClientLoginEvent $event
     */
    public function logoutCallback(ClientLoginEvent $event)
    {
        $app = $this->getContainer();
        $app['members.authenticate']->logout($event);
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
            new Provider\MembersServiceProvider(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendControllers()
    {
        $app = $this->getContainer();
        return [
            '/extend/members' => $app['members.controller.admin'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $app = $this->getContainer();
        $config = $this->getConfig();
        $base = '/' . ltrim($config['basepath'], '/');

        return [
            $base => $app['members.controller'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        $app = $this->getContainer();
        $options = ['is_safe' => ['html'], 'is_safe_callback' => true];

        return [
            'hasrole'    => [[$app['members.twig'], 'hasRole'], $options],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function isSafe()
    {
        return true;
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
