<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

/**
 * Token entity class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Token extends AbstractGuidEntity
{
    /** @var integer */
    protected $id;
    /** @var string */
    protected $token_type;
    /** @var string */
    protected $token;
    /** @var array */
    protected $token_data;
    /** @var integer */
    protected $expires;
    /** @var string */
    protected $cookie;

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->token_type;
    }

    /**
     * @param string $token_type
     */
    public function setTokenType($token_type)
    {
        $this->token_type = $token_type;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return array
     */
    public function getTokenData()
    {
        return $this->token_data;
    }

    /**
     * @param array $token_data
     */
    public function setTokenData($token_data)
    {
        $this->token_data = $token_data;
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param int $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return string
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param string $cookie
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }
}
