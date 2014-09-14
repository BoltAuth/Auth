<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\Bolt\ClientLogin\ClientRecords;
use Bolt\Extension\Bolt\ClientLogin\Session;

/**
 * Members Profiles
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MembersProfiles
{
    public function __construct(\Bolt\Application $app)
    {
        $this->app = $app;
    }

    public function getMembersProfiles($userid)
    {
        $records = new MembersRecords($this->app);

        $member = $records->getMember('id', $userid);

        if ($member) {
            $member['avatar']   = $records->getMemberMetaValue($userid, 'avatar');
            $member['location'] = $records->getMemberMetaValue($userid, 'location');

            return $member;
        }

        return $this->getDeletedUser();
    }

    private function getDeletedUser()
    {
        return array(
            'id' => -1,
            'username' => 'deleted',
            'email' => '',
            'displayname' => 'Deleted User',
            'lastseen' => '0000-00-00 00:00:00',
            'lastip' => '',
            'enabled' => 0,
            'roles' => '',
            'avatar' => 'http://placehold.it/350x150&text=Deleted+User',
            'location' => 'Unknown'
        );
    }
}
