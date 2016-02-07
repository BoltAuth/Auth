<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Admin;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Controller;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
use Bolt\Extension\Bolt\Members\Oauth2\Client\ProviderManager;
use Bolt\Extension\Bolt\Members\Oauth2\Handler;
use Bolt\Extension\Bolt\Members\Storage\Records;
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
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $this->registerBase($app);
        $this->registerControllers($app);
        $this->registerStorage($app);
        $this->registerForms($app);
        $this->registerOauthHandlers($app);
        $this->registerOauthProviders($app);

        $app['members.admin'] = $app->share(
            function ($app) {
                return new Admin($app['members.records'], $app['members.config'], $app['users']);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($this);
    }

    /**
     * {@inheritdoc}
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
        $app['members.config'] = $app->share(
            function () {
                return new Config($this->config);
            }
        );

        $app['members.debug'] = $app->protect(
            function ($app) {
                return $app['members.config']->isDebug();
            }
        );

        $app['members.feedback'] = $app->share(
            function ($app) {
                return new Feedback($app['session']);
            }
        );

        $app['members.roles'] = $app->share(
            function ($app) {
                return new AccessControl\Roles($app['members.config']);
            }
        );

        $app['members.session'] = $app->share(
            function ($app) {
                return new AccessControl\Session($app['members.records'], $app['session']);
            }
        );

        // Add the Twig Extension.
        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(new Twig\Functions($app['members.config'], $app['members.session'], $app['members.records'], $app['resources']));

                    return $twig;
                }
            )
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
            function ($app) {
                return new Controller\Authentication($app['members.config']);
            }
        );

        $app['members.controller.backend'] = $app->share(
            function ($app) {
                return new Controller\Backend($app['members.config']);
            }
        );

        $app['members.controller.frontend'] = $app->share(
            function ($app) {
                return new Controller\Frontend($app['members.config']);
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

        $mapping = [
            'members_account'      => ['Bolt\Extension\Bolt\Members\Storage\Entity\Account'     => 'Bolt\Extension\Bolt\Members\Storage\Repository\Account'],
            'members_account_meta' => ['Bolt\Extension\Bolt\Members\Storage\Entity\AccountMeta' => 'Bolt\Extension\Bolt\Members\Storage\Repository\AccountMeta'],
            'members_oauth'        => ['Bolt\Extension\Bolt\Members\Storage\Entity\Oauth'       => 'Bolt\Extension\Bolt\Members\Storage\Repository\Oauth'],
            'members_provider'     => ['Bolt\Extension\Bolt\Members\Storage\Entity\Provider'    => 'Bolt\Extension\Bolt\Members\Storage\Repository\Provider'],
            'members_token'        => ['Bolt\Extension\Bolt\Members\Storage\Entity\Token'       => 'Bolt\Extension\Bolt\Members\Storage\Repository\Token'],
        ];

        foreach ($mapping as $alias => $map) {
            $app['storage.repositories'] += $map;
            $app['storage.metadata']->setDefaultAlias($app['schema.prefix'] . $alias, key($map));
            $app['storage']->setRepository(key($map), current($map));
        }

        $app['members.records'] = $app->share(
            function () use ($app) {
                return new Records(
                    $app['storage']->getRepository('Bolt\Extension\Bolt\Members\Storage\Entity\Account'),
                    $app['storage']->getRepository('Bolt\Extension\Bolt\Members\Storage\Entity\AccountMeta'),
                    $app['storage']->getRepository('Bolt\Extension\Bolt\Members\Storage\Entity\Oauth'),
                    $app['storage']->getRepository('Bolt\Extension\Bolt\Members\Storage\Entity\Provider'),
                    $app['storage']->getRepository('Bolt\Extension\Bolt\Members\Storage\Entity\Token')
                );
            }
        );
    }

    private function registerForms(Application $app)
    {
        $app['members.forms'] = $app->share(
            function ($app) {
                $type = new \Pimple(
                    [
                        // @codingStandardsIgnoreStart
                        'login'    => $app->share(function () use ($app) { return new Form\Type\LoginType(); }),
                        'profile'  => $app->share(function () use ($app) { return new Form\Type\ProfileType(); }),
                        'register' => $app->share(function () use ($app) { return new Form\Type\RegisterType($app['members.records']); }),
                        // @codingStandardsIgnoreEnd
                    ]
                );
                $entity = new \Pimple(
                    [
                        // @codingStandardsIgnoreStart
                        'login'    => $app->share(function () use ($app) { return new Form\Entity\Login(); }),
                        'profile'  => $app->share(function () use ($app) { return new Form\Entity\Profile($app['members.records']); }),
                        'register' => $app->share(function () use ($app) { return new Form\Entity\Register(); }),
                        // @codingStandardsIgnoreEnd
                    ]
                );
                $constraint = new \Pimple(
                    [
                        // @codingStandardsIgnoreStart
                        'email' => $app->share(function () use ($app) { return new Form\Validator\Constraint\UniqueEmail($app['members.records']); }),
                        // @codingStandardsIgnoreEnd
                    ]
                );

                return new \Pimple([
                    'type'       => $type,
                    'entity'     => $entity,
                    'constraint' => $constraint,
                ]);
            }
        );

        $app['members.form.login'] = $app->share(
            function ($app) {
                return new Form\Login(
                    $app['form.factory'],
                    $app['members.forms']['type']['login'],
                    $app['members.forms']['entity']['login']
                );
            }
        );

        $app['members.form.profile'] = $app->share(
            function ($app) {
                return new Form\Profile(
                    $app['form.factory'],
                    $app['members.forms']['type']['profile'],
                    $app['members.forms']['entity']['profile']
                );
            }
        );

        $app['members.form.register'] = $app->share(
            function ($app) {
                return new Form\Register(
                    $app['form.factory'],
                    $app['members.forms']['type']['register'],
                    $app['members.forms']['entity']['register']
                );
            }
        );
    }

    private function registerOauthHandlers(Application $app)
    {
        // Authentication handler service.
        // Will be chosen, and set, inside a request cycle
        $app['members.oauth.handler'] = $app->share(
            function () {
                return new Handler\NullHandler();
            }
        );

        // Handler object for local authentication processing
        $app['members.oauth.handler.local'] = $app->protect(
            function ($app) use ($app) {
                return new Handler\Local($app['members.config'], $app);
            }
        );

        // Handler object for remote authentication processing
        $app['members.oauth.handler.remote'] = $app->protect(
            function ($app) use ($app) {
                return new Handler\Remote($app['members.config'], $app);
            }
        );
    }

    private function registerOauthProviders(Application $app)
    {
        // Provider manager
        $app['members.oauth.provider.manager'] = $app->share(
            function ($app) {
                $rootUrl = $app['resources']->getUrl('rooturl');

                return new ProviderManager($app['members.config'], $app['guzzle.client'], $app['logger.system'], $rootUrl);
            }
        );

        // OAuth provider service. Will be chosen, and set, inside a request cycle
        $app['members.oauth.provider'] = $app->share(
            function () {
                throw new \RuntimeException('Members authentication provider not set up!');
            }
        );

        $app['members.oauth.provider.name'] = $app->share(
            function () {
                throw new \RuntimeException('Members authentication provider not set up!');
            }
        );

        // Generic OAuth provider object
        $app['members.oauth.provider.generic'] = $app->protect(
            function () {
                return new Provider\Generic([]);
            }
        );

        // Local OAuth provider object
        $app['members.oauth.provider.local'] = $app->protect(
            function () {
                return new Provider\Local([]);
            }
        );

        // Provider objects for each enabled provider
        foreach ($this->config['providers'] as $providerName => $providerConfig) {
            if ($providerConfig['enabled'] === true) {
                $app['members.oauth.provider.' . strtolower($providerName)] = $app->protect(
                    function ($app) use ($app, $providerName) {
                        return $app['members.oauth.provider.manager']->getProvider($providerName);
                    }
                );
            }
        }
    }
}
