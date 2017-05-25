<?php

namespace Bolt\Extension\BoltAuth\Auth\EventListener;

use Bolt\Extension\BoltAuth\Auth\Event\AuthEvents;
use Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationEvent;
use Bolt\Extension\BoltAuth\Auth\Event\AuthProfileEvent;
use Bolt\Extension\BoltAuth\Auth\Handler\ProfileRegister;
use Bolt\Extension\BoltAuth\Auth\Handler\ProfileReset;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Profile events listener.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileListener implements EventSubscriberInterface
{
    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthEvents::AUTH_PROFILE_REGISTER => 'onProfileRegister',
            AuthEvents::AUTH_PROFILE_RESET    => 'onProfileReset',
        ];
    }

    /**
     * Profile registration event.
     *
     * @param AuthProfileEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function onProfileRegister(AuthProfileEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->getRegisterHandler()->handle($event, $eventName, $dispatcher);
    }

    /**
     * Password reset notification event.
     *
     * @param AuthNotificationEvent $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function onProfileReset(AuthNotificationEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->getResetHandler()->handle($event, $eventName, $dispatcher);
    }

    /**
     * @return ProfileRegister
     */
    private function getRegisterHandler()
    {
        return $this->app['auth.event_handler.profile_register'];
    }

    /**
     * @return ProfileReset
     */
    private function getResetHandler()
    {
        return $this->app['auth.event_handler.profile_reset'];
    }
}
