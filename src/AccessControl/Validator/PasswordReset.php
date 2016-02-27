<?php

namespace Bolt\Extension\Bolt\Members\AccessControl\Validator;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

/**
 * Password reset validation class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PasswordReset
{
    const COOKIE_NAME = 'members-password-reset';

    /** @var string */
    protected $cookieValue;
    /** @var string */
    protected $guid;

    /**
     * Return stored cookie value.
     *
     * @return string
     */
    public function getCookieValue()
    {
        return $this->cookieValue;
    }

    /**
     * Create a stored cookie value for later comparison.
     *
     * @return PasswordReset
     */
    public function setCookieValue()
    {
        $this->cookieValue = sha1(Uuid::uuid4()->toString());


        return $this;
    }

    /**
     * Return the account GUID for this request.
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Set the account GUID for this request.
     *
     * @param string $guid
     *
     * @return PasswordReset
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * Validate that the request cookie value matches our stored value.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function validate(Request $request)
    {
        if ($this->guid === null) {
            throw new \RuntimeException('Password reset validation can not occur without a stored GUID!');
        }
        if ($this->cookieValue === null) {
            throw new \RuntimeException('Password reset validation can not occur without a stored cookie!');
        }
        if ($request->cookies->get(self::COOKIE_NAME) === null) {
            return false;
        }

        $cookie = $request->cookies->get(self::COOKIE_NAME);

        return $cookie === $this->cookieValue;
    }
}
