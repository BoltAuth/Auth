<?php

namespace Bolt\Extension\Bolt\Membership;

/**
 *
 */
class Controller
{
    /**
     * @var Silex\Application
     */
    private $app;

    /**
     * Extension config array
     *
     * @var array
     */
    private $config;

    /**
     * @var Membership
     */
    private $membership;

    public function __construct(Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['extensions.' . Extension::NAME]->config;
        $this->membership = new Membership($this->app);
    }

    public function getMemberNew()
    {
    }

    public function getMemberProfile()
    {
    }

}
