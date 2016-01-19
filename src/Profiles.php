<?php

namespace Bolt\Extension\Bolt\Members;

use Silex\Application;

/**
 * Members Profiles
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Profiles
{
    /**
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get a member's profile
     *
     * @param integer $userid
     *
     * @return array
     */
    public function getMembersProfiles($userid)
    {
        $records = new Records($this->app);

        $member = $records->getMember('id', $userid);

        if ($member) {
            $member['avatar']   = $records->getMemberMetaValue($userid, 'avatar');
            $member['location'] = $records->getMemberMetaValue($userid, 'location');

            return $member;
        }

        return $this->getDeletedUser();
    }

    /**
     * Get a default array to account for a deleted user's data
     *
     * @return multitype:number string
     */
    private function getDeletedUser()
    {
        return [
            'id'          => -1,
            'username'    => 'deleted',
            'email'       => '',
            'displayname' => 'Deleted User',
            'lastseen'    => '0000-00-00 00:00:00',
            'lastip'      => '',
            'enabled'     => 0,
            'roles'       => '',
            'avatar'      => 'http://placehold.it/350x150&text=Deleted+User',
            'location'    => 'Unknown',
        ];
    }
}
