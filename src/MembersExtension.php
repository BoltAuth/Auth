<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\Bolt\Members\Provider\MembersServiceProvider;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\MenuTrait;
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
    use MenuTrait;

    /**
     * @inheritDoc
     */
    public function register(Application $app)
    {
        $this->extendMenuService();
    }

    /**
     * @inheritDoc
     */
    public function boot(Application $app)
    {
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultConfig()
    {
        return [
            'registration' => true,
            'urls'         => [
                'authentication' => 'authentication',
                'membership'     => 'membership',
            ],
        ];
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    protected function registerBackendControllers()
    {
        $app = $this->getContainer();

        return [
            '/extend/members' => $app['members.controller.backend'],
        ];
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new MembersServiceProvider($this->getConfig())
        ];
    }
}
