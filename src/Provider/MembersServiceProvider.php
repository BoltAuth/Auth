<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use Bolt\Extension\Bolt\Members\Members;
use Bolt\Extension\Bolt\Members\MembersExtension;
use Bolt\Extension\Bolt\Members\Profiles;
use Silex\Application;
use Silex\ServiceProviderInterface;

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

        $app['members.profiles'] = $app->share(
            function ($app) {
                $profiles = new Profiles($app);

                return $profiles;
            }
        );
    }

    public function boot(Application $app)
    {
    }
}
