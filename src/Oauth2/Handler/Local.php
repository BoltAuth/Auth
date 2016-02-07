<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Symfony\Component\HttpFoundation\Request;

/**
 * OAuth local login provider.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Local extends HandlerBase
{
    /**
     * {@inheritdoc}
     */
    public function login(Request $request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, $grantType = 'authorization_code')
    {
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request)
    {
    }
}
