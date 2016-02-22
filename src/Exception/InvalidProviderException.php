<?php

namespace Bolt\Extension\Bolt\Members\Exception;

class InvalidProviderException extends \Exception
{
    const INVALID_PROVIDER = 'Invalid provider.';
    const UNSET_PROVIDER = 'Unset provider.';
    const UNMAPPED_PROVIDER = 'Unmapped provider.';
}
