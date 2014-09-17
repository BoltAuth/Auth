<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;
use Bolt\Extension\Bolt\ClientLogin\Session;
use Bolt\Extension\Bolt\ClientLogin\ClientRecords;

/**
 * Member interface class
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
class Members
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
     * @var MembersRecords
     */
    private $records;

    public function __construct(Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
        $this->records = new MembersRecords($this->app);
    }

    /**
     * Check if we have this ClientLogin as a member
     *
     * @param  string      $provider   The provider, e.g. 'Google'
     * @param  string      $identifier The providers ID for the account
     * @return int|boolean The user ID of the member or false if not found
     */
    public function isMemberClientLogin($provider, $identifier)
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
    public function isMemberClientLoginAuth()
    {
        //
        $session = new Session();

        if ($session->doCheckLogin()) {
            return true;
        }

        return false;
    }

    /**
     * Get a member record
     *
     * @param  string        $field The user field to lookup the user by (id, username or email)
     * @param  string        $value Lookup value
     * @return array|boolean
     */
    public function getMember($field, $value)
    {
        if (! empty($field) && ! empty($value)) {
            $record = $this->records->getMember($field, $value);
            if ($record) {
                return $record;
            }
        }

        return false;
    }

    /**
     * Get a member's meta records
     *
     * @param  integer       $id   The user's ID
     * @param  string        $meta Optional meta value to limit to
     * @return array|boolean
     */
    public function getMemberMeta($id, $meta = false)
    {
        $records = $this->records->getMemberMeta($id, $meta);

        if ($records) {
            return $records;
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
    public function addMemberClientLoginProfile($userid, $provider, $identifier)
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
     * @param  array   $form
     * @param  array   $userdata The array of user data from ClientLogin
     * @return boolean
     */
    public function addMember($form, $userdata)
    {
        // Remember to look up email address and match new ClientLogin profiles
        // with existing Members

        $member = $this->getMember('email', $form['email']);

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
                $member = $this->getMember('email', $form['email']);

                // Add the provider info to meta
                $this->addMemberClientLoginProfile($member['id'], $userdata['provider'], $userdata['identifier']);

                // Add meta data from CLientLogin
                $this->records->updateMemberMeta($member['id'], 'avatar', $userdata['photoURL']);

                // Event dispatcher
                if ($this->app['dispatcher']->hasListeners('members.New')) {
                    $event = new MembersEvent();
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
    public function updateMemberLogin($userid)
    {
        if ($this->records->getMember('id', $userid)) {
            $this->records->updateMember($userid, array(
                'lastseen' => date('Y-m-d H:i:s'),
                'lastip'   => $this->app['request']->getClientIp()
            ));
        }
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

}
