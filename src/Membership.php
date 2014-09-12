<?php

namespace Bolt\Extension\Bolt\Membership;

use Silex;

/**
 *
 */
class Membership
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
     * @var MembershipRecords
     */
    private $records;

    public function __construct(Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['extensions.' . Extension::NAME]->config;
        $this->records = new MembershipRecords($this->app);
    }

    /**
     * Check if we have this ClientLogin as a member
     *
     * @param  string      $clientloginmeta In for format 'provider:identifier'
     * @return int|boolean The user ID of the member or false if not found
     */
    public function isMember($key)
    {
        $record = $this->records->getMetaRecords('clientlogin_key', $key, true);
        if ($record) {
            return $record['userid'];
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

}
