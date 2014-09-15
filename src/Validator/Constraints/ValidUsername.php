<?php

namespace Bolt\Extension\Bolt\Members\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidUsername extends Constraint
{
    public $message = 'The provided username: "%string%" exists, please choose a different one.';
}
