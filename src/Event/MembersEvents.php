<?php

namespace Bolt\Extension\Bolt\Members\Event;

/**
 * Members event constant class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
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
    const MEMBER_PROFILE_PRE_SAVE = 'member.profile.pre_save';
    const MEMBER_PROFILE_POST_SAVE = 'member.profile.post_save';
    const MEMBER_ROLE = 'member.role';

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }
}
