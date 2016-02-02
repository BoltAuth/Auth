<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Bolt\Extension\Bolt\Members\Exception\DisabledProviderException;
use Bolt\Extension\Bolt\Members\Exception\InvalidAuthorisationRequestException;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Request $request
     *
     * @throws DisabledProviderException
     * @throws InvalidAuthorisationRequestException
     *
     * @return Response
     */
    public function login(Request $request);

    /**
     * Process a OAuth2 provider login callback.
     *
     * @param Request $request
     * @param string  $grantType
     *
     * @return Response
     */
    public function process(Request $request, $grantType);

    /**
     * Logout a client.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function logout(Request $request);
}
