<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Form\MembersForms;

/**
 * Password reset submission type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileRecoverySubmitType extends AbstractProfileRecoveryType
{
    /** @var boolean */
    protected $requirePassword = true;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return MembersForms::PROFILE_RECOVERY_SUBMIT;
    }
}
