<?php

namespace Bolt\Extension\Bolt\Members\Provider;

use \Bolt\Extension\Bolt\Members\Members;
use Silex\Application;
use Silex\ServiceProviderInterface;

class MembersServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['members'] = $app->share(
            function ($app) {
                $members = new Members($app);

                return $members;
            }
        );
    }

    public function boot(Application $app)
    {
    }
}
