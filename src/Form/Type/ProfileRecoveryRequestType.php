<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Extension\BoltAuth\Auth\Form\AuthForms;

/**
 * Password reset request type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileRecoveryRequestType extends AbstractProfileRecoveryType
{
    /** @var boolean */
    protected $requirePassword = false;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AuthForms::PROFILE_RECOVERY_REQUEST;
    }
}
