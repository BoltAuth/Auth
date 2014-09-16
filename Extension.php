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
     * Extension's container
     *
     * @var string
     */
    const CONTAINER = 'extensions.Members';

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
            // Set up controller routes
            $this->app->mount('/' . $this->config['basepath'], new Controller\MembersController());

            // Twig functions
            $this->app['twig']->addExtension(new MembersTwigExtension($this->app));
        }

        /*
         * Hooks
         */
        $this->app['dispatcher']->addListener('clientlogin.Login',  array($this, 'loginCallback'));
        $this->app['dispatcher']->addListener('clientlogin.Logout', array($this, 'logoutCallback'));
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
        $member = $members->isMemberClientLogin($userdata['provider'], $userdata['identifier']);

        if ($member) {
            $members->updateMemberLogin($member);
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

            // Check to see if there is already a member with this email
            $member = $members->getMember('email', $providerdata['email']);

            if ($member) {
                // Associate this login with their Members profile
                $members->addMemberClientLoginProfile($member['id'], $userdata['provider'], $userdata['identifier']);
            } else {
                // Redirect to the 'new' page
                simpleredirect("/{$this->config['basepath']}/register");
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
                'parent'        => 'members.twig',
                'register'      => 'members_register.twig',
                'profile_edit'  => 'members_profile_edit.twig',
                'profile_view'  => 'members_profile_view.twig'
            ),
            'registration' => true,
            'csrf'         => true
        );
    }
}
