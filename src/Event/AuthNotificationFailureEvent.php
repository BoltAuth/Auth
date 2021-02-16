<?php

namespace Bolt\Extension\BoltAuth\Auth\Event;

use Swift_Mime_Message as SwiftMimeMessage;
use Swift_SwiftException as SwiftException;
use Symfony\Component\EventDispatcher\Event;

/**
 * Auth notification failure event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthNotificationFailureEvent extends Event
{
    /** @var SwiftMimeMessage */
    protected $message;
    /** @var SwiftException */
    protected $exception;

    /**
     * Constructor.
     *
     * @param SwiftMimeMessage $message
     * @param SwiftException   $exception
     */
    public function __construct(SwiftMimeMessage $message, SwiftException $exception)
    {
        $this->message = $message;
        $this->exception = $exception;
    }

    /**
     * @return SwiftMimeMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return SwiftException
     */
    public function getException()
    {
        return $this->exception;
    }
}
