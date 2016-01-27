<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use Bolt\Extension\Bolt\Members\Controller;
use Bolt\Extension\Bolt\Members\Storage\Schema\Manager;
use Bolt\Extension\Bolt\Members\Storage\Schema\Table;
use Bolt\Extension\Bolt\Members\Twig;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Members service provider.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersServiceProvider implements ServiceProviderInterface, EventSubscriberInterface
{
    /** @var array */
    private $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function register(Application $app)
    {
        $this->registerBase($app);
        $this->registerControllers($app);
        $this->registerStorage($app);
    }

    /**
     * @inheritDoc
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($this);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [];
    }

    /**
     * Register base services for Members.
     *
     * @param Application $app
     */
    private function registerBase(Application $app)
    {
        $app['members.twig'] = $app->share(
            function () {
                return new Twig\Functions($this->config);
            }
        );
    }

    /**
     * Register controller service providers.
     *
     * @param Application $app
     */
    private function registerControllers(Application $app)
    {
        $app['members.controller.authentication'] = $app->share(
            function () {
                return new Controller\Authentication($this->config);
            }
        );

        $app['members.controller.backend'] = $app->share(
            function () {
                return new Controller\Backend($this->config);
            }
        );

        $app['members.controller.frontend'] = $app->share(
            function () {
                return new Controller\Frontend($this->config);
            }
        );
    }

    /**
     * Register storage related service providers.
     *
     * @param Application $app
     */
    private function registerStorage(Application $app)
    {
        $app['members.schema.table'] = $app->share(
            function () use ($app) {
                /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
                $platform = $app['db']->getDatabasePlatform();

                // @codingStandardsIgnoreStart
                return new \Pimple([
                    'members_account'      => $app->share(function () use ($platform) { return new Table\Account($platform); }),
                    'members_account_meta' => $app->share(function () use ($platform) { return new Table\AccountMeta($platform); }),
                    'members_oauth'        => $app->share(function () use ($platform) { return new Table\Oauth($platform); }),
                    'members_provider'     => $app->share(function () use ($platform) { return new Table\Provider($platform); }),
                    'members_token'        => $app->share(function () use ($platform) { return new Table\Token($platform); }),
                ]);
                // @codingStandardsIgnoreEnd
            }
        );
    }
}
