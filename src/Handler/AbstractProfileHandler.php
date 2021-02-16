<?php

namespace Bolt\Extension\BoltAuth\Auth\Handler;

use Bolt\Extension\BoltAuth\Auth\Config\Config;
use Bolt\Extension\BoltAuth\Auth\Event\AuthEvents;
use Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationEvent;
use Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationFailureEvent;
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
     * @param AuthNotificationEvent $event
     * @param EventDispatcherInterface $dispatcher
     *
     * @return array
     */
    protected function queueMessage(SwiftMimeMessage $message, AuthNotificationEvent $event, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->dispatch(AuthEvents::AUTH_NOTIFICATION_PRE_SEND, $event);

        $failedRecipients = [];

        try {
            $this->mailer->send($message, $failedRecipients);
        } catch (\Swift_SwiftException $e) {
            // Dispatch an event
            $event = new AuthNotificationFailureEvent($message, $e);
            $dispatcher->dispatch(AuthEvents::AUTH_NOTIFICATION_FAILURE, $event);
        }

        return $failedRecipients;
    }
}
