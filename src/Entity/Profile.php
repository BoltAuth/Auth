<?php

namespace Bolt\Extension\Bolt\Members\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * User profile object class
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Profile
{
    /**
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2
     * )
     */
    protected $displayname;

    /**
     * @Assert\Email(
     *      message = 'The address "{{ value }}" is not a valid email.',
     *      checkMX = true
     * )
     */
    protected $email;

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getDisplayname()
    {
        return $this->displayname;
    }

    public function setDisplayname($displayname)
    {
        $this->displayname = $displayname;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }
}
