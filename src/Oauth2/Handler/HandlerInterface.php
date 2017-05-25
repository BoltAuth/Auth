<?php

namespace Bolt\Extension\BoltAuth\Auth\Oauth2\Handler;

use Bolt\Extension\BoltAuth\Auth\Exception\DisabledProviderException;
use Bolt\Extension\BoltAuth\Auth\Exception\InvalidAuthorisationRequestException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Authentication provider interface.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
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
     */
    public function login(Request $request);

    /**
     * Process a OAuth2 provider login callback.
     *
     * @param Request $request
     * @param string  $grantType
     */
    public function process(Request $request, $grantType);

    /**
     * Logout a client.
     *
     * @param Request $request
     */
    public function logout(Request $request);
}
