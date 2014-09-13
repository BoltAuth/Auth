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
        $this->app->match("{$this->config['basepath']}/register", array($this->controller, 'getMemberRegister'))
                    ->bind('getMemberRegister')
                    ->method('GET|POST');

        // Member profile
        $this->app->match("{$this->config['basepath']}/profile", array($this->controller, 'getMemberProfile'))
                    ->bind('getMemberProfile')
                    ->method('GET|POST');
    }

    /**
     * Hook for ClientLogin login events
     *
     * @param ClientLoginEvent $event
     */
    public function loginCallback(ClientLoginEvent $event)
    {
        $members = new Members($this->app);

        // Get the ClientLogin user data from the event
        $userdata = $event->getUser();

        // See if we have this in our database
        $id = $members->isMemberClientLogin($userdata['provider'], $userdata['identifier']);

        if ($id) {
            //
        } else {
            // If registration is closed, don't do anything
            if (! $this->config['registration']) {
// @TODO handle this properly
                return;
            }

            // Save any redirect that ClientLogin has pending
            $this->app['session']->set('pending',     $this->app['request']->get('redirect'));
            $this->app['session']->set('clientlogin', $userdata);

            $providerdata = json_decode($userdata['providerdata'], true);

            // Some providers (looking at you Twitter) don't supply an email
            if (empty($providerdata['email'] || $providerdata['displayName'])) {
                // Redirect to the 'new' page
                simpleredirect("/{$this->config['basepath']}/register");
            } else {
                // Check to see if there is already a member with this email
                $member = $members->getMember('email', $providerdata['email']);

                if ($member) {
                    // Associate this login with their Members profile
                    $members->addMemberClientLoginProfile($member['id'], $userdata['provider'], $userdata['identifier']);
                } else {
                    // Add the member to our database
                    $members->addMember($providerdata);
                }
            }
        }

    }

    /**
     * Hook for ClientLogin logout events
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
            'basepath' => 'members',
            'templates' => array(
                'parent'   => 'members.twig',
                'register' => 'members_register.twig',
                'profile'  => 'members_profile.twig'
            ),
            'registration' => true
        );
    }
}
