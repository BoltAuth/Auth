<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Members events
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersEvent extends Event
{
    /** @var array The user record */
    private $user;

    /**
     * @param array $user
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
