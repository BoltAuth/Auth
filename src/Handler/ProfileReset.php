<?php

namespace Bolt\Extension\BoltAuth\Auth\Handler;

use Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Profile handler base class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileReset extends AbstractProfileHandler
{
    /**
     * Password reset notification event.
     *
     * @param AuthNotificationEvent $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function handle(AuthNotificationEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        if ($event->isPropagationStopped()) {
            return;
        }

        $message = $event->getMessage();
        $this->queueMessage($message, $event, $dispatcher);
    }
}
