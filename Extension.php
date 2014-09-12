<?php

namespace Bolt\Extension\Bolt\Membership;

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
    const NAME = 'Membership';

    /**
     * @var Membership\Controller
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
            $records = new MembershipRecords($this->app);
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

        $membership = new Membership($this->app);

        if ($membership->isMember($key)) {
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
