<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Symfony\Component\HttpFoundation\Request;

/**
 * OAuth local login provider.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
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
