<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\Bolt\ClientLogin\Client;
use Bolt\Extension\Bolt\ClientLogin\Event\ClientLoginEvent;
use Bolt\Extension\Bolt\ClientLogin\Session;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Member authentication interface class
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Authenticate extends Controller\MembersController
{
    /** @var Application */
    private $app;
    /** array @var array */
    private $config;
    /** @var Records */
    private $records;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param array       $config
     */
    public function __construct(Application $app, array $config)
    {
        parent::__construct($config);

        $this->app = $app;
        $this->config = $config;
        $this->records = new Records($this->app);
    }

    /**
     * Authentication login processing
     *
     * @param ClientLoginEvent $event
     */
    public function login(ClientLoginEvent $event)
    {
        /** @var \Bolt\Extension\Bolt\ClientLogin\Client */
        $userdata = $event->getUser();

        // See if we have this in our database
        $member = $this->isMemberClientLogin($userdata->provider, $userdata->uid);

        if ($member) {
            $this->updateMemberLogin($member);
        } else {
            // If registration is closed, don't do anything
            if (! $this->config['registration']) {
                // @TODO handle this properly
                return;
            }

            // Save any redirect that ClientLogin has pending
            $this->app['clientlogin.session.handler']->set('pending',     $this->app['request']->get('redirect'));
            $this->app['clientlogin.session.handler']->set('clientlogin', $userdata->id);

            // Check to see if there is already a member with this email
            $member = $this->app['members']->getMember('email', $userdata->email);

            if ($member) {
                // Associate this login with their Members profile
                $this->addMemberClientLoginProfile($member['id'], $userdata->provider, $userdata->uid);
            } else {
                // Redirect to the 'new' page
                $this->app['clientlogin.session']->setResponse(new RedirectResponse("/{$this->config['basepath']}/register"));
            }
        }
    }

    /**
     * Authentication logout processing
     *
     * @param ClientLoginEvent $event
     */
    public function logout(ClientLoginEvent $event)
    {
    }

    /**
     * Test if a user has a valid ClientLogin session AND is a valid member
     *
     * @return boolean|integer Member ID, or false
     */
    public function isAuth()
    {
        // First check for ClientLogin auth
        if (! $this->app['clientlogin.session']->isLoggedIn()) {
            return false;
        }

        // Get their ClientLogin records
        $token = $this->app['clientlogin.session']->getToken(Session::TOKEN_SESSION);
        if (!$record = $this->app['clientlogin.db']->getUserProfileBySession($token)) {
            return false;
        }

        // Look them up internally
        return $this->isMemberClientLogin($record->provider, $record->uid);
    }

    /**
     * Check if we have this ClientLogin as a member
     *
     * @param string $provider   The provider, e.g. 'Google'
     * @param string $identifier The providers ID for the account
     *
     * @return int|boolean The user ID of the member or false if not found
     */
    private function isMemberClientLogin($provider, $identifier)
    {
        $key = 'clientlogin_id_' . strtolower($provider);
        $record = $this->records->getMetaRecords($key, $identifier, true);
        if ($record) {
            return $record['userid'];
        }

        return false;
    }

    /**
     * Check to see if a member is currently authenticated via ClientLogin
     *
     * @return boolean
     */
    private function isMemberClientLoginAuth()
    {
        //
        if ($this->app['clientlogin.session']->isLoggedIn()) {
            return true;
        }

        return false;
    }

    /**
     * Add a ClientLogin key to a user's profile
     *
     * @param integer $userid     A user's ID
     * @param string  $provider   The login provider
     * @param string  $identifier Provider's unique ID for the user
     *
     * @return bool
     */
    private function addMemberClientLoginProfile($userid, $provider, $identifier)
    {
        if ($this->records->getMember('id', $userid)) {
            $key = 'clientlogin_id_' . strtolower($provider);
            $this->records->updateMemberMeta($userid, $key, $identifier);

            return true;
        }

        return false;
    }

    /**
     * Add a new member to the database
     *
     * @param array  $form
     * @param Client $userdata The user data from ClientLogin
     *
     * @return boolean
     */
    protected function addMember($form, Client $userdata)
    {
        // Remember to look up email address and match new ClientLogin profiles
        // with existing Members

        $member = $this->app['members']->getMember('email', $form['email']);

        if ($member) {
            // We already have them, just link the profile
            $this->addMemberClientLoginProfile($member['id'], $userdata->provider, $userdata->uid);
        } else {
            //
            $create = $this->records->updateMember(false, [
                'username'    => $form['username'],
                'email'       => $form['email'],
                'displayname' => $form['displayname'],
                'lastseen'    => date('Y-m-d H:i:s'),
                'lastip'      => $this->app['request']->getClientIp(),
                'enabled'     => 1,
            ]);

            if ($create) {
                // Get the new record
                $member = $this->app['members']->getMember('email', $form['email']);

                // Add the provider info to meta
                $this->addMemberClientLoginProfile($member['id'], $userdata->provider, $userdata->uid);

                // Add meta data from ClientLogin
                $this->records->updateMemberMeta($member['id'], 'avatar', $userdata->imageUrl);

                // Event dispatcher
                if ($this->app['dispatcher']->hasListeners('members.New')) {
                    $event = new Event\MembersEvent();
                    $this->app['dispatcher']->dispatch('members.New', $event);
                }
            }
        }

        return true;
    }

    /**
     * Update a members login meta
     *
     * @param integer $userid
     */
    private function updateMemberLogin($userid)
    {
        if ($this->records->getMember('id', $userid)) {
            $this->records->updateMember($userid, [
                'lastseen' => date('Y-m-d H:i:s'),
                'lastip'   => $this->app['request']->getClientIp(),
            ]);
        }
    }
}
