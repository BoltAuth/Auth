<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\Bolt\Members\Provider\MembersServiceProvider;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\MenuTrait;
use Bolt\Extension\TwigTrait;
use Bolt\Menu\MenuEntry;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Silex\ServiceProviderInterface;
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
    use ConfigTrait;
    use ControllerMountTrait;
    use DatabaseSchemaTrait;
    use MenuTrait;
    use TwigTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $this->extendMenuService();
        $this->extendTwigService();
        $this->extendDatabaseSchemaServices();
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($this);
        $app['dispatcher']->addSubscriber($app['members.admin']);
        $app['dispatcher']->addSubscriber($app['members.roles']);
        $this->container = $app;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'registration' => true,
            'roles' => [
                'admin'  => [
                    'root'
                ],
                'member' => [
                    'admin' => 'Administrator',
                ],
            ],
            'urls'         => [
                'authentication' => 'authentication',
                'membership'     => 'membership',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $app = $this->getContainer();
        $config = (array) $this->getConfig();

        return [
            $config['urls']['authentication'] => $app['members.controller.authentication'],
            $config['urls']['membership']     => $app['members.controller.frontend'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendControllers()
    {
        $app = $this->getContainer();

        return [
            '/' => $app['members.controller.backend'],
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
    protected function registerTwigPaths()
    {
        return ['templates'];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        $app = $this->getContainer();
        $options = ['is_safe' => ['html'], 'is_safe_callback' => true];

        return [
            'members_auth'   => [[$app['members.twig'], 'displayAuth'], $options],
            'members_login'  => [[$app['members.twig'], 'displayLogin'], $options],
            'members_logout' => [[$app['members.twig'], 'displayLogout'], $options],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ControllerEvents::MOUNT => [
                ['onMountControllers', -150],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new MembersServiceProvider($this->getConfig())
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerExtensionTables()
    {
        $app = $this->getContainer();

        return [
            'members_account'      => $app['members.schema.table']['members_account'],
            'members_account_meta' => $app['members.schema.table']['members_account_meta'],
            'members_oauth'        => $app['members.schema.table']['members_oauth'],
            'members_provider'     => $app['members.schema.table']['members_provider'],
            'members_token'        => $app['members.schema.table']['members_token'],
        ];
    }
}
