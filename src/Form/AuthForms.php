<?php

namespace Bolt\Extension\BoltAuth\Auth\Form;

/**
 * Auth form constants.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthForms
{
    const ASSOCIATE = 'form_associate';
    const LOGIN_OAUTH = 'form_login_oauth';
    const LOGIN_PASSWORD = 'form_login_password';
    const LOGOUT = 'form_logout';
    const PROFILE_EDIT = 'form_profile_edit';
    const PROFILE_RECOVERY_REQUEST = 'form_profile_recovery_request';
    const PROFILE_RECOVERY_SUBMIT = 'form_profile_recovery_submit';
    const PROFILE_REGISTER = 'form_profile_register';
    const PROFILE_VIEW = 'form_profile_view';

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }
}
