<?php

namespace Bolt\Extension\Bolt\Members\EventListener;

use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Handler\ProfileRegister;
use Bolt\Extension\Bolt\Members\Handler\ProfileReset;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Profile events listener.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
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
            MembersEvents::MEMBER_PROFILE_REGISTER => 'onProfileRegister',
            MembersEvents::MEMBER_PROFILE_RESET    => 'onProfileReset',
        ];
    }

    /**
     * Profile registration event.
     *
     * @param MembersProfileEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function onProfileRegister(MembersProfileEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->getRegisterHandler()->handle($event, $eventName, $dispatcher);
    }

    /**
     * Password reset notification event.
     *
     * @param MembersNotificationEvent $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function onProfileReset(MembersNotificationEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->getResetHandler()->handle($event, $eventName, $dispatcher);
    }

    /**
     * @return ProfileRegister
     */
    private function getRegisterHandler()
    {
        return $this->app['members.event_handler.profile_register'];
    }

    /**
     * @return ProfileReset
     */
    private function getResetHandler()
    {
        return $this->app['members.event_handler.profile_reset'];
    }
}
