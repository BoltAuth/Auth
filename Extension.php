<?php

namespace Bolt\Extension\Bolt\Members;

use \Bolt\Extension\Bolt\ClientLogin\ClientLoginEvent;

/**
 *
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Extension extends \Bolt\BaseExtension
{
    /**
     * @var string Extension name
     */
    const NAME = 'Members';

    /**
     * @var Members\Controller
     */
    private $controller;

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize()
    {
        /*
         * Backend
         */
        if ($this->app['config']->getWhichEnd() == 'backend') {
            // Check & create database tables if required
            $records = new MembersRecords($this->app);
            $records->dbCheck();
        }

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() == 'frontend') {
            // Set up routes
            $this->setController();
        }

        /*
         * Hooks
         */
        $this->app['dispatcher']->addListener('clientlogin.Login',  array($this, 'loginCallback'));
        $this->app['dispatcher']->addListener('clientlogin.Logout', array($this, 'logoutCallback'));
    }

    /**
     * Create controller and define routes
     */
    private function setController()
    {
        // Create controller object
        $this->controller = new Controller($this->app);

        // New member
        $this->app->match("{$this->config['basepath']}/new", array($this->controller, 'getMemberNew'))
                    ->bind('getMemberNew')
                    ->method('GET|POST');

        // Member profile
        $this->app->match("{$this->config['basepath']}/profile", array($this->controller, 'getMemberProfile'))
                    ->bind('getMemberProfile')
                    ->method('GET|POST');
    }

    /**
     *
     * @param ClientLoginEvent $event
     */
    public function loginCallback(ClientLoginEvent $event)
    {
        $userdata = $event->getUser();
        $key = strtolower($userdata['provider']) . ':' . $userdata['identifier'];

        $members = new Members($this->app);

        if ($members->isMember($key)) {
            //
        }

    }

    /**
     *
     * @param ClientLoginEvent $event
     */
    public function logoutCallback(ClientLoginEvent $event)
    {
    }

    /**
     * Default config options
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return array(
            'basepath' => 'members'
        );
    }
}
