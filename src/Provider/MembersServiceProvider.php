<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Admin;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Controller;
use Bolt\Extension\Bolt\Members\EventListener;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
use Bolt\Extension\Bolt\Members\Oauth2\Client\ProviderManager;
use Bolt\Extension\Bolt\Members\Oauth2\Handler;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Extension\Bolt\Members\Storage\Repository;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Extension\Bolt\Members\Storage\Schema\Table;
use Bolt\Extension\Bolt\Members\Twig;
use Pimple as Container;
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
        $this->registerEventListeners($app);

        $app['members.meta_fields'] = [];

        // Add the Twig Extension.
        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(
                        new Twig\Functions(
                            $app['members.config'],
                            $app['members.forms.manager'],
                            $app['members.records'],
                            $app['members.session']
                        )
                    );

                    return $twig;
                }
            )
        );

        $app['safe_twig'] = $app->share(
            $app->extend(
                'safe_twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(
                        new Twig\Functions(
                            $app['members.config'],
                            $app['members.forms.manager'],
                            $app['members.records'],
                            $app['members.session']
                        )
                    );

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

        $app['members.admin'] = $app->share(
            function ($app) {
                return new Admin($app['members.records'], $app['members.config'], $app['users']);
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
        $app['members.records'] = $app->share(
            function () use ($app) {
                /** @var Repository\Account $accountRepo */
                $accountRepo = $app['storage']->getRepository(Entity\Account::class);
                /** @var Repository\AccountMeta $accountMetaRepo */
                $accountMetaRepo = $app['storage']->getRepository(Entity\AccountMeta::class);
                /** @var Repository\Oauth $oauthRepo */
                $oauthRepo = $app['storage']->getRepository(Entity\Oauth::class);
                /** @var Repository\Provider $providerRepo */
                $providerRepo = $app['storage']->getRepository(Entity\Provider::class);
                /** @var Repository\Token $tokenRepo */
                $tokenRepo = $app['storage']->getRepository(Entity\Token::class);

                return new Records(
                    $accountRepo,
                    $accountMetaRepo,
                    $oauthRepo,
                    $providerRepo,
                    $tokenRepo
                );
            }
        );
    }

    private function registerForms(Application $app)
    {
        $app['members.form.components'] = $app->share(
            function ($app) {
                $type = new Container(
                    [
                        // @codingStandardsIgnoreStart
                        'associate'        => $app->share(function () use ($app) { return new Form\Type\AssociateType($app['members.config']); }),
                        'login_oauth'      => $app->share(function () use ($app) { return new Form\Type\LoginOauthType($app['members.config']); }),
                        'login_password'   => $app->share(function () use ($app) { return new Form\Type\LoginPasswordType($app['members.config']); }),
                        'logout'           => $app->share(function () use ($app) { return new Form\Type\LogoutType($app['members.config']); }),
                        'profile_edit'     => $app->share(function () use ($app) { return new Form\Type\ProfileEditType($app['members.config']); }),
                        'profile_recovery' => $app->share(function () use ($app) { return new Form\Type\ProfileRecoveryType($app['members.config']); }),
                        'profile_register' => $app->share(function () use ($app) { return new Form\Type\ProfileRegisterType($app['members.config'], $app['members.records'], $app['members.session']); }),
                        // @codingStandardsIgnoreEnd
                    ]
                );
                $entity = new Container(
                    [
                        // @codingStandardsIgnoreStart
                        'profile' => $app->share(function () use ($app) { return new Form\Entity\Profile(); }),
                        // @codingStandardsIgnoreEnd
                    ]
                );
                $constraint = new Container(
                    [
                        // @codingStandardsIgnoreStart
                        'email' => $app->share(function () use ($app) { return new Form\Validator\Constraint\UniqueEmail($app['members.records']); }),
                        // @codingStandardsIgnoreEnd
                    ]
                );

                return new Container([
                    'type'       => $type,
                    'entity'     => $entity,
                    'constraint' => $constraint,
                ]);
            }
        );

        $app['members.form.associate'] = $app->share(
            function ($app) {
                return new Form\Associate(
                    $app['form.factory'],
                    $app['members.form.components']['type']['associate'],
                    $app['members.form.components']['entity']['profile']
                );
            }
        );

        $app['members.form.login_oauth'] = $app->share(
            function ($app) {
                return new Form\LoginOauth(
                    $app['form.factory'],
                    $app['members.form.components']['type']['login_oauth'],
                    $app['members.form.components']['entity']['profile']
                );
            }
        );

        $app['members.form.login_password'] = $app->share(
            function ($app) {
                return new Form\LoginPassword(
                    $app['form.factory'],
                    $app['members.form.components']['type']['login_password'],
                    $app['members.form.components']['entity']['profile']
                );
            }
        );

        $app['members.form.logout'] = $app->share(
            function ($app) {
                return new Form\Logout(
                    $app['form.factory'],
                    $app['members.form.components']['type']['logout'],
                    $app['members.form.components']['entity']['profile']
                );
            }
        );

        $app['members.form.profile_edit'] = $app->share(
            function ($app) {
                return new Form\ProfileEdit(
                    $app['form.factory'],
                    $app['members.form.components']['type']['profile_edit'],
                    $app['members.form.components']['entity']['profile']
                );
            }
        );

        $app['members.form.profile_recovery'] = $app->share(
            function ($app) {
                return new Form\ProfileRecovery(
                    $app['form.factory'],
                    $app['members.form.components']['type']['profile_recovery'],
                    $app['members.form.components']['entity']['profile']
                );
            }
        );

        $app['members.form.profile_register'] = $app->share(
            function ($app) {
                return new Form\ProfileRegister(
                    $app['form.factory'],
                    $app['members.form.components']['type']['profile_register'],
                    $app['members.form.components']['entity']['profile']
                );
            }
        );

        $app['members.forms'] = $app->share(
            function ($app) {
                $forms = [
                    'form' => new Container([
                        'associate'        => $app->share(function () use ($app) { return $app['members.form.associate']; }),
                        'login_oauth'      => $app->share(function () use ($app) { return $app['members.form.login_oauth']; }),
                        'login_password'   => $app->share(function () use ($app) { return $app['members.form.login_password']; }),
                        'logout'           => $app->share(function () use ($app) { return $app['members.form.logout']; }),
                        'profile_edit'     => $app->share(function () use ($app) { return $app['members.form.profile_edit']; }),
                        'profile_recovery' => $app->share(function () use ($app) { return $app['members.form.profile_recovery']; }),
                        'profile_register' => $app->share(function () use ($app) { return $app['members.form.profile_register']; }),
                    ]),
                    'renderer' => $app->share(function () use ($app) { return $app['twig']; }),
                ];

                return new Container($forms);
            }
        );

        $app['members.forms.manager'] = $app->share(
            function ($app) {
                return new Form\Manager(
                    $app['members.config'],
                    $app['members.session'],
                    $app['members.feedback'],
                    $app['members.records'],
                    $app['members.forms']
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

        $app['members.oauth.provider.map'] = $app->share(
            function () {
                return [
                    'facebook'  => '\Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\Facebook',
                    'generic'   => '\Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\Generic',
                    'github'    => '\Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\GitHub',
                    'google'    => '\Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\Google',
                    'instagram' => '\Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\Instagram',
                    'linkedin'  => '\Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\LinkedIn',
                    'local'     => '\Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\Local',
                    'microsoft' => '\Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\Microsoft',
                ];
            }
        );
    }

    private function registerEventListeners(Application $app)
    {
        $app['members.listener.profile'] = $app->share(
            function ($app) {
                return new EventListener\ProfileListener(
                    $app['members.config'],
                    $app['twig'],
                    $app['mailer'],
                    $app['dispatcher'],
                    $app['resources']->getUrl('rooturl')
                );
            }
        );
    }
}
