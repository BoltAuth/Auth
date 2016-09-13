<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersRolesEvent;
use Bolt\Extension\Bolt\Members\Provider\MembersServiceProvider;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Extension\Bolt\Members\Storage\Repository;
use Bolt\Extension\Bolt\Members\Storage\Schema\Table;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\MenuTrait;
use Bolt\Extension\StorageTrait;
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
    use ConfigTrait;
    use ControllerMountTrait;
    use DatabaseSchemaTrait;
    use MenuTrait;
    use StorageTrait;
    use TwigTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $this->extendMenuService();
        $this->extendTwigService();
        $this->extendDatabaseSchemaServices();
        $this->extendRepositoryMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $this->container = $app;
        $this->subscribe($app['dispatcher']);
    }

    /**
     * Define events to listen to here.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $app = $this->getContainer();
        $dispatcher->addSubscriber($this);
        $dispatcher->addSubscriber($app['members.admin']);
        $dispatcher->addSubscriber($app['members.feedback']);
        $dispatcher->addSubscriber($app['members.roles']);
        $dispatcher->addSubscriber($app['members.session']);
        $dispatcher->addSubscriber($app['members.listener.profile']);
        $dispatcher->dispatch(MembersEvents::MEMBER_ROLE, new MembersRolesEvent());
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $app = $this->getContainer();

        return [
            $app['members.config']->getUrlAuthenticate() => $app['members.controller.authentication'],
            $app['members.config']->getUrlMembers()      => $app['members.controller.membership'],
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
        $roles = isset($config['roles']['admin']) ? $config['roles']['admin'] : ['root'];

        return [
            (new MenuEntry('members', 'members'))
                ->setLabel(Trans::__('Members'))
                ->setIcon('fa:users')
                ->setPermission(implode('||', $roles)),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return [
            'templates'       => ['position' => 'append', 'namespace' => 'Members'],
            'templates/admin' => ['position' => 'append', 'namespace' => 'MembersAdmin'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ControllerEvents::MOUNT => [
                ['onMountControllers', 100],
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
            new MembersServiceProvider($this->getConfig()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerExtensionTables()
    {
        return [
            'members_account'      => Table\Account::class,
            'members_account_meta' => Table\AccountMeta::class,
            'members_oauth'        => Table\Oauth::class,
            'members_provider'     => Table\Provider::class,
            'members_token'        => Table\Token::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerRepositoryMappings()
    {
        return [
            'members_account'      => [Entity\Account::class     => Repository\Account::class],
            'members_account_meta' => [Entity\AccountMeta::class => Repository\AccountMeta::class],
            'members_oauth'        => [Entity\Oauth::class       => Repository\Oauth::class],
            'members_provider'     => [Entity\Provider::class    => Repository\Provider::class],
            'members_token'        => [Entity\Token::class       => Repository\Token::class],
        ];
    }
}
