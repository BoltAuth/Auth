<?php

namespace Bolt\Extension\Bolt\Members\Exception;

/**
 * Account verification exception.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccountVerificationException extends \RuntimeException
{
    const MISSING_META = 1;
    const REMOVED_META = 2;
    const MISSING_ACCOUNT = 4;
}
