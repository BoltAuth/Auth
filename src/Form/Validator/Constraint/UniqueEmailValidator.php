<?php

namespace Bolt\Extension\Bolt\Members\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Simple validator to check if a email address already exists.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class UniqueEmailValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /** @var UniqueEmail $constraint */
        $records = $constraint->getRecords();
        if ($records->getAccountByEmail($value)) {
            $this->context->addViolation(
                $constraint->message,
                ['%string%' => $value]
            );
        }
    }
}
