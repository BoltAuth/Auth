<?php

namespace Bolt\Extension\BoltAuth\Auth\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractController implements ControllerProviderInterface
{
    use AuthServicesTrait;

    /** @var Application */
    private $app;

    /**
     * @param Request     $request
     * @param Application $app
     */
    public function boot(Request $request, Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = $app['controllers_factory'];
        $ctr->before([$this, 'boot']);

        return $ctr;
    }

    /**
     * @internal Should only be used by traits.
     *
     * {@inheritdoc}
     */
    protected function getContainer()
    {
        return $this->app;
    }
}
