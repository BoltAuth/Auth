<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;
use Bolt\Extension\Bolt\ClientLogin\Session;

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
     * @param  string      $key In for format 'provider:identifier'
     * @return int|boolean The user ID of the member or false if not found
     */
    public function isMemberClientLogin($key)
    {
        $record = $this->records->getMetaRecords('clientlogin_key', $key, true);
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
        $record = $this->records->getMember($field, $value);
        if ($record) {
            return $record;
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

    public function newMember()
    {
        //

        // Event dispatcher
        if ($this->app['dispatcher']->hasListeners('members.New')) {
            $event = new MembersEvent();
            $this->app['dispatcher']->dispatch('members.New', $event);
        }
    }

}
