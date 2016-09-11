<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use Bolt\Extension\Bolt\Members\Security\TokenAuthenticator;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Security service provider.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SecurityServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['security.token_authenticator'] = $app->share(
            function ($app) {
                return new TokenAuthenticator($app['members.records']);
            }
        );

        $app['security.firewalls'] = [
            'membership' => [
                'pattern'   => '^/membership/.+',
                'anonymous' => true,
                'form'      => [
                    'login_path' => '/authentication/login',
                    'check_path' => '/authentication/login_check', ],
                'logout'  => [
                    'logout_path' => '/authentication/logout',
                ],
                'guard' => [
                    'authenticators' => [
                        'security.token_authenticator',
                    ],
                ],
            ],
        ];

        $app['request_matcher'] = $app->share(
            function ($app) {
                return $app['url_matcher'];
            }
        );

        $app->register(new SilexSecurityServiceProvider());

        $app['dispatcher']->addListener(KernelEvents::REQUEST, [$app['security.firewall'], 'onKernelRequest']);
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
