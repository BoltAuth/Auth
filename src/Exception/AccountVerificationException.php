<?php

namespace Bolt\Extension\Bolt\Members\Exception;

/**
 * Account verification exception.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AccountVerificationException extends \RuntimeException
{
    const MISSING_META = 1;
    const REMOVED_META = 2;
    const MISSING_ACCOUNT = 4;
}
