<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;

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
     * @var Members
     */
    private $members;

    public function __construct(Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['extensions.' . Extension::NAME]->config;
        $this->members = new Members($this->app);
    }

    public function getMemberNew()
    {
        return 'New user page';
    }

    public function getMemberProfile()
    {
        return 'User profile page';
    }

}
