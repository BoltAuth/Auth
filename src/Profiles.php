<?php

namespace Bolt\Extension\Bolt\Members;

use Silex\Application;

/**
 * Members Profiles
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
        return array(
            'id'          => -1,
            'username'    => 'deleted',
            'email'       => '',
            'displayname' => 'Deleted User',
            'lastseen'    => '0000-00-00 00:00:00',
            'lastip'      => '',
            'enabled'     => 0,
            'roles'       => '',
            'avatar'      => 'http://placehold.it/350x150&text=Deleted+User',
            'location'    => 'Unknown'
        );
    }
}
