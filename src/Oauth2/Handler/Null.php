<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

/**
 * OAuth null provider.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Null implements HandlerInterface
{
    public function __construct()
    {
        throw new \RuntimeException('Members OAuth authentication handler not set up!');
    }

    /**
     * {@inheritdoc}
     */
    public function login()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process($grantType = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
    }
}
