<?php

namespace Bolt\Extension\BoltAuth\Auth\Provider;

use Bolt\Extension\BoltAuth\Auth\AccessControl;
use Bolt\Extension\BoltAuth\Auth\Admin;
use Bolt\Extension\BoltAuth\Auth\Config\Config;
use Bolt\Extension\BoltAuth\Auth\Controller;
use Bolt\Extension\BoltAuth\Auth\Feedback;
use Bolt\Extension\BoltAuth\Auth\Form;
use Bolt\Extension\BoltAuth\Auth\Handler as EventHandler;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Client\ProviderManager;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Handler;
use Bolt\Extension\BoltAuth\Auth\Storage;
use Bolt\Extension\BoltAuth\Auth\Storage\Entity;
use Bolt\Extension\BoltAuth\Auth\Storage\Records;
use Bolt\Extension\BoltAuth\Auth\Twig;
use Bolt\Storage\Database\Schema\Comparison\IgnoredChange;
use Bolt\Version as BoltVersion;
use Pimple as Container;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Auth service provider.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthServiceProvider implements ServiceProviderInterface, EventSubscriberInterface
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
        $this->registerEventHandlers($app);

        /** @deprecated. Do not use */
        $app['auth.meta_fields'] = [];

        $app['session'] = $app->share(
            $app->extend(
                'session',
                function (SessionInterface $session) use ($app) {
                    if (!$session->isStarted()) {
                        $session->registerBag($app['auth.feedback']);
                    }
                    return $session;
                }
            )
        );

        if (version_compare(BoltVersion::forComposer(), '3.3.0', '<')) {
            if (!isset($app['twig.runtimes'])) {
                $app['twig.runtimes'] = function () {
                    return [];
                };
            }
            if (!isset($app['twig.runtime_loader'])) {
                $app['twig.runtime_loader'] = function ($app) {
                    return new Twig\RuntimeLoader($app, $app['twig.runtimes']);
                };
            }
        }

        $app['twig.runtime.auth'] = function ($app) {
            return new Twig\Extension\AuthRuntime(
                $app['auth.config'],
                $app['auth.forms.manager'],
                $app['auth.records'],
                $app['auth.session'],
                $app['url_generator.lazy']
            );
        };

        $app['twig.runtimes'] = $app->extend(
            'twig.runtimes',
            function (array $runtimes) {
                return $runtimes + [
                        Twig\Extension\AuthRuntime::class => 'twig.runtime.auth',
                    ];
            }
        );

        // Add the Twig Extension.
        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(new Twig\Extension\AuthExtension());

                    if (version_compare(BoltVersion::forComposer(), '3.3.0', '<')) {
                        $twig->addRuntimeLoader($app['twig.runtime_loader']);
                    }

                    return $twig;
                }
            )
        );

        $app['safe_twig'] = $app->share(
            $app->extend(
                'safe_twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(new Twig\Extension\AuthExtension());

                    return $twig;
                }
            )
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
     * Register base services for Auth.
     *
     * @param Application $app
     */
    private function registerBase(Application $app)
    {
        $app['auth.config'] = $app->share(
            function () {
                return new Config($this->config);
            }
        );

        $app['auth.feedback'] = $app->share(
            function ($app) {
                return new Feedback('_auth', $app['auth.config']->isDebug());
            }
        );

        $app['auth.roles'] = $app->share(
            function ($app) {
                return new AccessControl\Roles($app['auth.config'], $app['dispatcher']);
            }
        );

        $app['auth.session'] = $app->share(
            function ($app) {
                return new AccessControl\Session($app['auth.records'], $app['session'], $app['url_generator']);
            }
        );

        $app['auth.admin'] = $app->share(
            function ($app) {
                return new Admin\Manager($app['auth.records'], $app['auth.config'], $app['users'], $app['dispatcher']);
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
        $app['auth.controller.authentication'] = $app->share(
            function () {
                return new Controller\Authentication();
            }
        );

        $app['auth.controller.backend'] = $app->share(
            function () {
                return new Controller\Backend();
            }
        );

        $app['auth.controller.auth'] = $app->share(
            function () {
                return new Controller\Auth();
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
        $app['auth.repositories'] = $app->share(
            function () use ($app) {
                return new Container(
                    [
                        // @codingStandardsIgnoreStart
                        'account' => $app->share(function () use ($app) {
                            return $app['storage']->getRepository(Entity\Account::class);
                        }),
                        'account_meta' => $app->share(function () use ($app) {
                            return $app['storage']->getRepository(Entity\AccountMeta::class);
                        }),
                        'oauth' => $app->share(function () use ($app) {
                            return $app['storage']->getRepository(Entity\Oauth::class);
                        }),
                        'provider' => $app->share(function () use ($app) {
                            return $app['storage']->getRepository(Entity\Provider::class);
                        }),
                        'token' => $app->share(function () use ($app) {
                            return $app['storage']->getRepository(Entity\Token::class);
                        }),
                        // @codingStandardsIgnoreEnd
                    ]
                );
            }
        );

        $app['auth.records'] = $app->share(
            function () use ($app) {
                return new Records($app['auth.repositories']);
            }
        );

        $app['schema.comparator'] = $app->extend(
            'schema.comparator',
            function ($comparator) {
                $comparator->addIgnoredChange(new IgnoredChange('changedColumns', 'type', 'bigint', 'integer'));

                return $comparator;
            }
        );

        $app['auth.records.profile'] = $app->share(
            function ($app) {
                return new Storage\FormEntityHandler($app['auth.config'], $app['auth.records'], $app['dispatcher'], $app['auth.session']);
            }
        );
    }

    private function registerForms(Application $app)
    {
        $app['auth.form.types'] = $app->share(
            function ($app) {
                return new Container(
                    [
                        'associate' => $app->share(
                            function () use ($app) {
                                return new Form\Type\AssociateType($app['auth.config']);
                            }
                        ),
                        'login_oauth' => $app->share(
                            function () use ($app) {
                                return new Form\Type\LoginOauthType($app['auth.config']);
                            }
                        ),
                        'login_password' => $app->share(
                            function () use ($app) {
                                return new Form\Type\LoginPasswordType($app['auth.config']);
                            }
                        ),
                        'logout' => $app->share(
                            function () use ($app) {
                                return new Form\Type\LogoutType($app['auth.config']);
                            }
                        ),
                        'profile_edit' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileEditType($app['auth.config']);
                            }
                        ),
                        'profile_recovery_request' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileRecoveryRequestType($app['auth.config']);
                            }
                        ),
                        'profile_recovery_submit' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileRecoverySubmitType($app['auth.config']);
                            }
                        ),
                        'profile_register' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileRegisterType(
                                    $app['auth.config'],
                                    $app['auth.records'],
                                    $app['auth.session']
                                );
                            }
                        ),
                        'profile_view' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileViewType($app['auth.config']);
                            }
                        ),
                    ]
                );
            }
        );

        $app['auth.form.generator'] = $app->share(
            function ($app) {
                return new Form\Generator(
                    $app['auth.config'],
                    $app['form.factory'],
                    $app['dispatcher']
                );
            }
        );

        $app['auth.forms.manager'] = $app->share(
            function ($app) {
                return new Form\Manager(
                    $app['auth.config'],
                    $app['auth.session'],
                    $app['auth.feedback'],
                    $app['auth.records'],
                    $app['auth.form.generator'],
                    $app['url_generator']
                );
            }
        );
    }

    private function registerOauthHandlers(Application $app)
    {
        // Authentication handler service.
        // Will be chosen, and set, inside a request cycle
        $app['auth.oauth.handler'] = $app->share(
            function () {
                return new Handler\NullHandler();
            }
        );

        // Handler object for local authentication processing
        $app['auth.oauth.handler.local'] = $app->protect(
            function () use ($app) {
                return new Handler\Local($app['auth.config'], $app);
            }
        );

        // Handler object for remote authentication processing
        $app['auth.oauth.handler.remote'] = $app->protect(
            function () use ($app) {
                return new Handler\Remote($app['auth.config'], $app);
            }
        );
    }

    private function registerOauthProviders(Application $app)
    {
        // Provider manager
        $app['auth.oauth.provider.manager'] = $app->share(
            function ($app) {
                return new ProviderManager($app['auth.config'], $app['guzzle.client'], $app['logger.system'], $app['url_generator.lazy']);
            }
        );

        // OAuth provider service. Will be chosen, and set, inside a request cycle
        $app['auth.oauth.provider'] = $app->share(
            function () {
                throw new \RuntimeException('Auth authentication provider not set up!');
            }
        );

        $app['auth.oauth.provider.name'] = $app->share(
            function () {
                throw new \RuntimeException('Auth authentication provider not set up!');
            }
        );

        // Local OAuth provider object
        $app['auth.oauth.provider.local'] = $app->protect(
            function () {
                return new Provider\Local([]);
            }
        );

        // Provider objects for each enabled provider
        foreach ($this->config['providers'] as $providerName => $providerConfig) {
            if ($providerConfig['enabled'] === true) {
                $app['auth.oauth.provider.' . strtolower($providerName)] = $app->protect(
                    function () use ($app, $providerName) {
                        return $app['auth.oauth.provider.manager']->getProvider($providerName);
                    }
                );
            }
        }

        $app['auth.oauth.provider.map'] = $app->share(
            function () {
                return [
                    'facebook'  => Provider\Facebook::class,
                    'generic'   => Provider\Generic::class,
                    'github'    => Provider\GitHub::class,
                    'google'    => Provider\Google::class,
                    'instagram' => Provider\Instagram::class,
                    'linkedin'  => Provider\LinkedIn::class,
                    'local'     => Provider\Local::class,
                    'microsoft' => Provider\Microsoft::class,
                    'wpoauth'   => Provider\WpOauth::class,
                ];
            }
        );
    }

    private function registerEventHandlers(Application $app)
    {
        $app['auth.event_handler.profile_register'] = $app->share(
            function ($app) {
                return new EventHandler\ProfileRegister(
                    $app['auth.config'],
                    $app['twig'],
                    $app['mailer'],
                    $app['url_generator.lazy']
                );
            }
        );

        $app['auth.event_handler.profile_reset'] = $app->share(
            function ($app) {
                return new EventHandler\ProfileReset(
                    $app['auth.config'],
                    $app['twig'],
                    $app['mailer'],
                    $app['url_generator.lazy']
                );
            }
        );
    }
}
