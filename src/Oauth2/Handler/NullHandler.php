<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Symfony\Component\HttpFoundation\Request;

/**
 * OAuth null provider.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class NullHandler implements HandlerInterface
{
    public function __construct()
    {
        if (php_sapi_name() !== 'cli') {
            throw new \RuntimeException('Members OAuth authentication handler not set up!');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function login(Request $request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, $grantType = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request)
    {
    }
}
