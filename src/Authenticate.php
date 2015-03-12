<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\Bolt\ClientLogin\ClientLoginEvent;
use Bolt\Extension\Bolt\ClientLogin\ClientRecords;
use Bolt\Extension\Bolt\ClientLogin\Session;
use Bolt\Library as Lib;
use Silex;
use Silex\Application;

/**
 * Member authentication interface class
 *
 * Copyright (C) 2014  Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class Authenticate extends Controller\MembersController
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
     * @var Records
     */
    private $records;

    /**
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
        $this->records = new Records($this->app);
    }

    /**
     * Authentication login processing
     *
     * @param ClientLoginEvent $event
     */
    public function login(ClientLoginEvent $event)
    {
        // Get the ClientLogin user data from the event
        $userdata = $event->getUser();

        // See if we have this in our database
        $member = $this->isMemberClientLogin($userdata['provider'], $userdata['identifier']);

        if ($member) {
            $this->updateMemberLogin($member);
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
            $member = $this->app['members']->getMember('email', $providerdata['email']);

            if ($member) {
                // Associate this login with their Members profile
                $this->addMemberClientLoginProfile($member['id'], $userdata['provider'], $userdata['identifier']);
            } else {
                // Redirect to the 'new' page
                Lib::simpleredirect("/{$this->config['basepath']}/register");
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
        $session = new Session($this->app);
        if (! $session->doCheckLogin()) {
            return false;
        }

        // Get their ClientLogin records
        $records = new ClientRecords($this->app);
        $record = $records->getUserProfileBySession($session->token);
        if (! $record) {
            return false;
        }

        // Look them up internally
        return $this->isMemberClientLogin($records->user['provider'], $records->user['identifier']);
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
        $session = new Session();

        if ($session->doCheckLogin()) {
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
     * @param array $form
     * @param array $userdata The array of user data from ClientLogin
     *
     * @return boolean
     */
    protected function addMember($form, $userdata)
    {
        // Remember to look up email address and match new ClientLogin profiles
        // with existing Members

        $member = $this->app['members']->getMember('email', $form['email']);

        if ($member) {
            // We already have them, just link the profile
            $this->addMemberClientLoginProfile($member['id'], $userdata['provider'], $userdata['identifier']);
        } else {
            //
            $create = $this->records->updateMember(false, array(
                'username'    => $form['username'],
                'email'       => $form['email'],
                'displayname' => $form['displayname'],
                'lastseen'    => date('Y-m-d H:i:s'),
                'lastip'      => $this->app['request']->getClientIp(),
                'enabled'     => 1
            ));

            if ($create) {
                // Get the new record
                $member = $this->app['members']->getMember('email', $form['email']);

                // Add the provider info to meta
                $this->addMemberClientLoginProfile($member['id'], $userdata['provider'], $userdata['identifier']);

                // Add meta data from CLientLogin
                $this->records->updateMemberMeta($member['id'], 'avatar', $userdata['photoURL']);

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
            $this->records->updateMember($userid, array(
                'lastseen' => date('Y-m-d H:i:s'),
                'lastip'   => $this->app['request']->getClientIp()
            ));
        }
    }
}
