<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Bolt\Extension\Bolt\Members\Exception\DisabledProviderException;
use Bolt\Extension\Bolt\Members\Exception\InvalidAuthorisationRequestException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication provider interface.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
interface HandlerInterface
{
    /**
     * Login a client.
     *
     * @throws DisabledProviderException
     * @throws InvalidAuthorisationRequestException
     *
     * @return Response
     */
    public function login();

    /**
     * Process a OAuth2 provider login callback.
     *
     * @param string $grantType
     *
     * @return Response
     */
    public function process($grantType);

    /**
     * Logout a client.
     *
     * @return Response
     */
    public function logout();
}
