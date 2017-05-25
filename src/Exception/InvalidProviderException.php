<?php

namespace Bolt\Extension\BoltAuth\Auth\Exception;

class InvalidProviderException extends \Exception
{
    const INVALID_PROVIDER = 'Invalid provider.';
    const UNSET_PROVIDER = 'Unset provider.';
    const UNMAPPED_PROVIDER = 'Unmapped provider.';
}
