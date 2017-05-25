<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Swift_Mime_Message as SwiftMimeMessage;
use Symfony\Component\EventDispatcher\Event;

/**
 * Members notification event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersNotificationEvent extends Event
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
