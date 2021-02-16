<?php

namespace Bolt\Extension\BoltAuth\Auth\Event;

/**
 * Auth event constant class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthEvents
{
    const AUTH_LOGIN = 'auth.login';
    const AUTH_LOGIN_FAILED_ACCOUNT_DISABLED = 'auth.login.failed.account_disabled';
    const AUTH_LOGOUT = 'auth.logout';
    const AUTH_ENABLE = 'auth.enable';
    const AUTH_DISABLE = 'auth.disable';
    const AUTH_PROFILE_EDIT = 'auth.profile.edit';
    const AUTH_PROFILE_PRE_SAVE = 'auth.profile.pre_save';
    const AUTH_PROFILE_POST_SAVE = 'auth.profile.post_save';
    const AUTH_PROFILE_REGISTER = 'auth.profile.register';
    const AUTH_PROFILE_RESET = 'auth.profile.reset';
    const AUTH_PROFILE_VERIFY = 'auth.profile.verify';
    const AUTH_PROFILE_DELETE = 'auth.profile.delete';
    const AUTH_ROLE = 'auth.role';
    const AUTH_NOTIFICATION_PRE_SEND = 'auth.notification.pre_send';
    const AUTH_NOTIFICATION_FAILURE = 'auth.notification.failure';

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }
}
