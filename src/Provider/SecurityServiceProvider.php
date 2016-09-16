<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use Bolt\Extension\Bolt\Members\Security\CookieAuthenticator;
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
        $app['security.cookie_authenticator'] = $app->share(
            function ($app) {
                return new CookieAuthenticator($app['members.session'], $app['members.records'], $app['url_generator.lazy']);
            }
        );

        $app['security.firewalls'] = [
            'membership' => [
                //'pattern'   => '^/membership/.+',
                'pattern'   => '^/membership/profile/edit',
                'anonymous' => true,
                'form'      => [
                    'login_path' => '/authentication/login',
                    'check_path' => '/authentication/login_check', ],
                'logout'  => [
                    'logout_path' => '/authentication/logout',
                ],
                'guard' => [
                    'authenticators' => [
                        'security.cookie_authenticator',
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
