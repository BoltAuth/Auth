<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

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
        return 'profile_recovery';
    }
}
