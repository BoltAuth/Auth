<?php

namespace Bolt\Extension\Bolt\Members\Event;

/**
 * Members event constant class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MembersEvents
{
    const MEMBER_LOGIN = 'member.login';
    const MEMBER_LOGOUT = 'member.logout';
    const MEMBER_REGISTER = 'member.register';
    const MEMBER_UNREGISTER = 'member.unregister';
    const MEMBER_ENABLE = 'member.enable';
    const MEMBER_DISBALE = 'member.disable';
    const MEMBER_PROFILE_EDIT = 'member.profile.edit';
    const MEMBER_PROFILE_SAVE = 'member.profile.save';
    const MEMBER_ROLE = 'member.role';

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }
}
