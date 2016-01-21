<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use Bolt\Extension\Bolt\Members\Authenticate;
use Bolt\Extension\Bolt\Members\Controller;
use Bolt\Extension\Bolt\Members\Members;
use Bolt\Extension\Bolt\Members\MembersExtension;
use Bolt\Extension\Bolt\Members\Profiles;
use Bolt\Extension\Bolt\Members\Records;
use Bolt\Extension\Bolt\Members\Twig;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MembersServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['members'] = $app->share(
            function ($app) {
                /** @var MembersExtension $extension */
                $extension = $app['extensions']->get('Bolt/Members');
                $members = new Members($app, $extension->getConfig());

                return $members;
            }
        );

        $app['members.authenticate'] = $app->share(
            function ($app) {
                /** @var MembersExtension $extension */
                $extension = $app['extensions']->get('Bolt/Members');
                $records = new Authenticate($app, $extension->getConfig());

                return $records;
            }
        );

        $app['members.profiles'] = $app->share(
            function ($app) {
                $profiles = new Profiles($app);

                return $profiles;
            }
        );

        $app['members.records'] = $app->share(
            function ($app) {
                $records = new Records($app);

                return $records;
            }
        );

        $app['members.controller'] = $app->share(
            function ($app) {
                /** @var MembersExtension $extension */
                $extension = $app['extensions']->get('Bolt/Members');
                $controller = new Controller\MembersController($extension->getConfig());

                return $controller;
            }
        );

        $app['members.controller.admin'] = $app->share(
            function ($app) {
                /** @var MembersExtension $extension */
                $extension = $app['extensions']->get('Bolt/Members');
                $controller = new Controller\MembersAdminController($app, $extension->getConfig());

                return $controller;
            }
        );

        $app['members.twig'] = $app->share(
            function ($app) {
                $twig = new Twig\MembersExtension($app);

                return $twig;
            }
        );
    }

    public function boot(Application $app)
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $app['dispatcher'];
        $dispatcher->addSubscriber($app['members.authenticate']);
    }
}
