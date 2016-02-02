<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Token entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Token extends AbstractGuidEntity
{
    protected $id;
    protected $token_type;
    protected $token;
    protected $token_data;
    protected $expires;
    protected $cookie;

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return $this->token_type;
    }

    /**
     * @param mixed $token_type
     */
    public function setTokenType($token_type)
    {
        $this->token_type = $token_type;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getTokenData()
    {
        return $this->token_data;
    }

    /**
     * @param mixed $token_data
     */
    public function setTokenData($token_data)
    {
        $this->token_data = $token_data;
    }

    /**
     * @return mixed
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param mixed $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return mixed
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param mixed $cookie
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }
}
