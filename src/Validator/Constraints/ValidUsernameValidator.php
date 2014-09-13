<?php

namespace Bolt\Extension\Bolt\Members\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Bolt\Extension\Bolt\Members\MembersRecords;

/**
 * Simple validator to check if a user name exists
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ValidUsernameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // Get Bolt
        $app = \Bolt\Configuration\ResourceManager::getApp();

        // Get our records interface
        $records = new MembersRecords($app);

        if ($records->getMember('username', $value)) {
            $this->context->addViolation(
                $constraint->message,
                array('%string%' => $value)
            );
        }
    }
}