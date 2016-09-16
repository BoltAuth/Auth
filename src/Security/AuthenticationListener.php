<?php

namespace Bolt\Extension\Bolt\Members\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

class AuthenticationListener extends AbstractAuthenticationListener
{
    /**
     * @inheritDoc
     */
    protected function attemptAuthentication(Request $request)
    {
    }
}
