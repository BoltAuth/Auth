<?php

namespace Bolt\Extension\Bolt\Members\Form;

/**
 * Members form constants.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MembersForms
{
    const FORM_ASSOCIATE = 'associate';
    const FORM_LOGIN_OAUTH = 'login_oauth';
    const FORM_LOGIN_PASSWORD = 'login_password';
    const FORM_LOGOUT = 'logout';
    const FORM_PROFILE_EDIT = 'profile_edit';
    const FORM_PROFILE_RECOVER_REQUEST = 'profile_recovery_request';
    const FORM_PROFILE_RECOVER_SUBMIT = 'profile_recovery_submit';
    const FORM_PROFILE_REGISTER = 'profile_register';
    const FORM_PROFILE_VIEW = 'profile_view';

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }
}
