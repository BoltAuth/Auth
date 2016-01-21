<?php

namespace Bolt\Extension\Bolt\Members\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ValidUsername extends Constraint
{
    public $message = 'The provided username: "%string%" exists, please choose a different one.';
}
