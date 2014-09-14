<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;
use Bolt\Extension\Bolt\ClientLogin\Session;
use Bolt\Extension\Bolt\ClientLogin\ClientRecords;

/**
 *
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
        $this->config = $this->app['extensions.' . Extension::NAME]->config;
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
     * Check to see if a member is currently authenticated
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
     * @param integer $userid A user's ID
     * @param string  $key    The ClientLogin key in the format 'provider:identifier'
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
     * @param array $form
     * @return boolean
     */
    public function addMember($form)
    {
        // Remember to look up email address and match new ClientLogin profiles
        // with existing Members

        $member = $this->getMember('email', $form['email']);

        if ($member) {
            // We already have them, just link the profile
            $this->addMemberClientLoginProfile($member['id'], $form['provider'], $form['identifier']);
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
                $this->addMemberClientLoginProfile($member['id'], $form['provider'], $form['identifier']);

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
