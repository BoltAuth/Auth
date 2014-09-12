<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt;
use Symfony\Component\EventDispatcher\Event;

class MembersEvent extends Event
{
    /**
     * The user record
     *
     * @var array
     */
    private $user;

    /**
     * @param array  $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Return the user record
     */
    public function getUser()
    {
        return $this->user;
    }
}
