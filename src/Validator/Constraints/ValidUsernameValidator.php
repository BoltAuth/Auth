<?php

namespace Bolt\Extension\Bolt\Members\Validator\Constraints;

use Bolt\Configuration\ResourceManager;
use Bolt\Extension\Bolt\Members\Records;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Simple validator to check if a user name exists
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ValidUsernameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // Get Bolt
        $app = ResourceManager::getApp();

        // Get our records interface
        $records = new Records($app);

        if ($records->getMember('username', $value)) {
            $this->context->addViolation(
                $constraint->message,
                ['%string%' => $value]
            );
        }
    }
}
