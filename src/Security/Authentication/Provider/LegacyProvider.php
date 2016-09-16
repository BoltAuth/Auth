<?php

namespace Bolt\Extension\Bolt\Members\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LegacyProvider implements AuthenticationProviderInterface
{
    /**
     * @inheritDoc
     */
    public function authenticate(TokenInterface $token)
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(TokenInterface $token)
    {
        return true;
    }
}
