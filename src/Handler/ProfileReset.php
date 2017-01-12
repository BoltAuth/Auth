<?php

namespace Bolt\Extension\Bolt\Members\Handler;

use Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Profile handler base class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileReset extends AbstractProfileHandler
{
    /**
     * Password reset notification event.
     *
     * @param MembersNotificationEvent $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function handle(MembersNotificationEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        if ($event->isPropagationStopped()) {
            return;
        }

        $message = $event->getMessage();
        $this->queueMessage($message, $event, $dispatcher);
    }
}
