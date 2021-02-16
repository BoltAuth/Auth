<?php

namespace Bolt\Extension\BoltAuth\Auth\Event;

use Swift_Mime_Message as SwiftMimeMessage;
use Symfony\Component\EventDispatcher\Event;

/**
 * Auth notification event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthNotificationEvent extends Event
{
    /** @var SwiftMimeMessage */
    protected $message;

    /**
     * Constructor.
     *
     * @param SwiftMimeMessage $message
     */
    public function __construct(SwiftMimeMessage $message)
    {
        $this->message = $message;
    }

    /**
     * @return SwiftMimeMessage
     */
    public function getMessage()
    {
        return $this->message;
    }
}
