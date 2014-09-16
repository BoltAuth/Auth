<?php

namespace Bolt\Extension\Bolt\Members\Entity;

use Bolt\Extension\Bolt\Members\Validator\Constraints\ValidUsername;
use Symfony\Component\Validator\Constraints as Assert;

class Reply
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