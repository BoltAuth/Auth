<?php

namespace Bolt\Extension\BoltAuth\Auth;

use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\BoltAuth\Auth\AccessControl\SessionSubscriber;
use Bolt\Extension\BoltAuth\Auth\Provider\AuthServiceProvider;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity;
use Bolt\Extension\BoltAuth\Auth\Storage\Repository;
use Bolt\Extension\BoltAuth\Auth\Storage\Schema\Table;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\MenuTrait;
use Bolt\Extension\StorageTrait;
use Bolt\Extension\TwigTrait;
use Bolt\Extension\TranslationTrait;
use Bolt\Menu\MenuEntry;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Auth management extension for Bolt
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthExtension extends AbstractExtension implements ServiceProviderInterface, EventSubscriberInterface
{
    use ConfigTrait;
    use ControllerMountTrait;
    use DatabaseSchemaTrait;
    use MenuTrait;
    use StorageTrait;
    use TwigTrait;
    use TranslationTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $this->extendMenuService();
        $this->extendTwigService();
        $this->extendDatabaseSchemaServices();
        $this->extendRepositoryMapping();
        $this->extendTranslatorService();
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
        $dispatcher->addSubscriber($app['auth.admin']);
        $dispatcher->addSubscriber(new SessionSubscriber($app));
        $dispatcher->addSubscriber(new EventListener\ProfileListener($app));
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $app = $this->getContainer();

        return [
            $app['auth.config']->getUrlAuthenticate() => $app['auth.controller.authentication'],
            $app['auth.config']->getUrlAuth()      => $app['auth.controller.auth'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendControllers()
    {
        $app = $this->getContainer();

        return [
            '/' => $app['auth.controller.backend'],
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
            (new MenuEntry('auth', 'auth'))
                ->setLabel(Trans::__('Auth'))
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
            'templates'       => ['position' => 'append', 'namespace' => 'Auth'],
            'templates/admin' => ['position' => 'append', 'namespace' => 'AuthAdmin'],
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
            new AuthServiceProvider($this->getConfig()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerExtensionTables()
    {
        return [
            'auth_account'      => Table\Account::class,
            'auth_account_meta' => Table\AccountMeta::class,
            'auth_oauth'        => Table\Oauth::class,
            'auth_provider'     => Table\Provider::class,
            'auth_token'        => Table\Token::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerRepositoryMappings()
    {
        return [
            'auth_account'      => [Entity\Account::class     => Repository\Account::class],
            'auth_account_meta' => [Entity\AccountMeta::class => Repository\AccountMeta::class],
            'auth_oauth'        => [Entity\Oauth::class       => Repository\Oauth::class],
            'auth_provider'     => [Entity\Provider::class    => Repository\Provider::class],
            'auth_token'        => [Entity\Token::class       => Repository\Token::class],
        ];
    }
}
