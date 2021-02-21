<?php

namespace Bolt\Extension\Bolt\Members\Handler;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationFailureEvent;
use Swift_Mailer as SwiftMailer;
use Swift_Mime_Message as SwiftMimeMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment as TwigEnvironment;

/**
 * Profile handler base class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractProfileHandler
{
    /** @var Config */
    protected $config;
    /** @var TwigEnvironment */
    protected $twig;
    /** @var SwiftMailer */
    protected $mailer;
    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /**
     * Constructor.
     *
     * @param Config                $config
     * @param TwigEnvironment       $twig
     * @param SwiftMailer           $mailer
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Config $config, TwigEnvironment $twig, SwiftMailer $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->config = $config;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param SwiftMimeMessage         $message
     * @param MembersNotificationEvent $event
     * @param EventDispatcherInterface $dispatcher
     *
     * @return array
     */
    protected function queueMessage(SwiftMimeMessage $message, MembersNotificationEvent $event, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->dispatch(MembersEvents::MEMBER_NOTIFICATION_PRE_SEND, $event);

        $failedRecipients = [];

        try {
            $this->mailer->send($message, $failedRecipients);
        } catch (\Swift_SwiftException $e) {
            // Dispatch an event
            $event = new MembersNotificationFailureEvent($message, $e);
            $dispatcher->dispatch(MembersEvents::MEMBER_NOTIFICATION_FAILURE, $event);
        }

        return $failedRecipients;
    }
}
