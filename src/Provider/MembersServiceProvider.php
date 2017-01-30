<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Admin;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Controller;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Handler as EventHandler;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
use Bolt\Extension\Bolt\Members\Oauth2\Client\ProviderManager;
use Bolt\Extension\Bolt\Members\Oauth2\Handler;
use Bolt\Extension\Bolt\Members\Storage;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Extension\Bolt\Members\Twig;
use Bolt\Storage\Database\Schema\Comparison\IgnoredChange;
use Bolt\Version as BoltVersion;
use Pimple as Container;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
        $this->registerEventHandlers($app);

        /** @deprecated. Do not use */
        $app['members.meta_fields'] = [];

        $app['session'] = $app->extend(
            'session',
            function (SessionInterface $session) use ($app) {
                $session->registerBag($app['members.feedback']);

                return $session;
            }
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

        $app['twig.runtime.members'] = function ($app) {
            return new Twig\Extension\MembersRuntime(
                $app['members.config'],
                $app['members.forms.manager'],
                $app['members.records'],
                $app['members.session'],
                $app['url_generator.lazy']
            );
        };

        $app['twig.runtimes'] = $app->extend(
            'twig.runtimes',
            function (array $runtimes) {
                return $runtimes + [
                        Twig\Extension\MembersRuntime::class => 'twig.runtime.members',
                    ];
            }
        );

        // Add the Twig Extension.
        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(new Twig\Extension\MembersExtension());

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
                    $twig->addExtension(new Twig\Extension\MembersExtension());

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

        $app['members.feedback'] = $app->share(
            function ($app) {
                return new Feedback('_members', $app['members.config']->isDebug());
            }
        );

        $app['members.roles'] = $app->share(
            function ($app) {
                return new AccessControl\Roles($app['members.config'], $app['dispatcher']);
            }
        );

        $app['members.session'] = $app->share(
            function ($app) {
                return new AccessControl\Session($app['members.records'], $app['session'], $app['url_generator']->generate('homepage'));
            }
        );

        $app['members.admin'] = $app->share(
            function ($app) {
                return new Admin\Manager($app['members.records'], $app['members.config'], $app['users'], $app['dispatcher']);
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
                return new Controller\Authentication();
            }
        );

        $app['members.controller.backend'] = $app->share(
            function () {
                return new Controller\Backend();
            }
        );

        $app['members.controller.membership'] = $app->share(
            function () {
                return new Controller\Membership();
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
        $app['members.repositories'] = $app->share(
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

        $app['members.records'] = $app->share(
            function () use ($app) {
                return new Records($app['members.repositories']);
            }
        );

        $app['schema.comparator'] = $app->extend(
            'schema.comparator',
            function ($comparator) {
                $comparator->addIgnoredChange(new IgnoredChange('changedColumns', 'type', 'bigint', 'integer'));

                return $comparator;
            }
        );

        $app['members.records.profile'] = $app->share(
            function ($app) {
                return new Storage\FormEntityHandler($app['members.config'], $app['members.records'], $app['dispatcher'], $app['members.session']);
            }
        );
    }

    private function registerForms(Application $app)
    {
        $app['members.form.types'] = $app->share(
            function ($app) {
                return new Container(
                    [
                        'associate' => $app->share(
                            function () use ($app) {
                                return new Form\Type\AssociateType($app['members.config']);
                            }
                        ),
                        'login_oauth' => $app->share(
                            function () use ($app) {
                                return new Form\Type\LoginOauthType($app['members.config']);
                            }
                        ),
                        'login_password' => $app->share(
                            function () use ($app) {
                                return new Form\Type\LoginPasswordType($app['members.config']);
                            }
                        ),
                        'logout' => $app->share(
                            function () use ($app) {
                                return new Form\Type\LogoutType($app['members.config']);
                            }
                        ),
                        'profile_edit' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileEditType($app['members.config']);
                            }
                        ),
                        'profile_recovery_request' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileRecoveryRequestType($app['members.config']);
                            }
                        ),
                        'profile_recovery_submit' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileRecoverySubmitType($app['members.config']);
                            }
                        ),
                        'profile_register' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileRegisterType(
                                    $app['members.config'],
                                    $app['members.records'],
                                    $app['members.session']
                                );
                            }
                        ),
                        'profile_view' => $app->share(
                            function () use ($app) {
                                return new Form\Type\ProfileViewType($app['members.config']);
                            }
                        ),
                    ]
                );
            }
        );

        $app['members.form.generator'] = $app->share(
            function ($app) {
                return new Form\Generator(
                    $app['members.config'],
                    $app['form.factory'],
                    $app['dispatcher']
                );
            }
        );

        $app['members.forms.manager'] = $app->share(
            function ($app) {
                return new Form\Manager(
                    $app['members.config'],
                    $app['members.session'],
                    $app['members.feedback'],
                    $app['members.records'],
                    $app['members.form.generator'],
                    $app['url_generator']
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
            function () use ($app) {
                return new Handler\Local($app['members.config'], $app);
            }
        );

        // Handler object for remote authentication processing
        $app['members.oauth.handler.remote'] = $app->protect(
            function () use ($app) {
                return new Handler\Remote($app['members.config'], $app);
            }
        );
    }

    private function registerOauthProviders(Application $app)
    {
        // Provider manager
        $app['members.oauth.provider.manager'] = $app->share(
            function ($app) {
                return new ProviderManager($app['members.config'], $app['guzzle.client'], $app['logger.system'], $app['url_generator.lazy']);
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
                    function () use ($app, $providerName) {
                        return $app['members.oauth.provider.manager']->getProvider($providerName);
                    }
                );
            }
        }

        $app['members.oauth.provider.map'] = $app->share(
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
        $app['members.event_handler.profile_register'] = $app->share(
            function ($app) {
                return new EventHandler\ProfileRegister(
                    $app['members.config'],
                    $app['twig'],
                    $app['mailer'],
                    $app['url_generator.lazy']
                );
            }
        );

        $app['members.event_handler.profile_reset'] = $app->share(
            function ($app) {
                return new EventHandler\ProfileReset(
                    $app['members.config'],
                    $app['twig'],
                    $app['mailer'],
                    $app['url_generator.lazy']
                );
            }
        );
    }
}
