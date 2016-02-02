<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\Entity\Profile;
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
    private $member;

    /**
     * @param array $member
     */
    public function __construct(Profile $member)
    {
        $this->member = $member;
    }

    /**
     * Return the user record
     */
    public function getMember()
    {
        return $this->member;
    }
}
