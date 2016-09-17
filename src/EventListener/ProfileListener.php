<?php

namespace Bolt\Extension\Bolt\Members\EventListener;

use Bolt\Extension\Bolt\Members\AccessControl\Validator\AccountVerification;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationFailureEvent;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Swift_Mailer as SwiftMailer;
use Swift_Mime_Message as SwiftMimeMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig_Environment as TwigEnvironment;

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
    /** @var Config */
    private $config;
    /** @var TwigEnvironment */
    private $twig;
    /** @var SwiftMailer */
    private $mailer;
    /** @var string */
    private $siteUrl;

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param TwigEnvironment          $twig
     * @param SwiftMailer              $mailer
     * @param string                   $siteUrl
     */
    public function __construct(Config $config, TwigEnvironment $twig, SwiftMailer $mailer, $siteUrl)
    {
        $this->config = $config;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->siteUrl = $siteUrl;
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
        $from = [$this->config->getNotificationEmail() => $this->config->getNotificationName()];
        $email = [$event->getAccount()->getEmail() => $event->getAccount()->getDisplayname()];
        $subject = $this->twig->render($this->config->getTemplate('verification', 'subject'), ['member' => $event->getAccount()]);
        $mailHtml = $this->getRegisterHtml($event);

        /** @var SwiftMimeMessage $message */
        $message = $this->mailer
            ->createMessage('message')
            ->setSubject($subject)
            ->setBody(strip_tags($mailHtml))
            ->addPart($mailHtml, 'text/html')
        ;

        try {
            $message
                ->setFrom($from)
                ->setReplyTo($from)
                ->setTo($email)
            ;
        } catch (\Swift_RfcComplianceException $e) {
            // Dispatch an event
            $event = new MembersNotificationFailureEvent($message, $e);
            $dispatcher->dispatch(MembersEvents::MEMBER_NOTIFICATION_FAILURE, $event);

            return;
        }

        $event = new MembersNotificationEvent($message);
        $this->queueMessage($message, $event, $dispatcher);
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
        if ($event->isPropagationStopped()) {
            return;
        }

        $message = $event->getMessage();
        $this->queueMessage($message, $event, $dispatcher);
    }

    /**
     * @param SwiftMimeMessage         $message
     * @param MembersNotificationEvent $event
     *
     * @param EventDispatcherInterface $dispatcher
     *
     * @return array
     */
    private function queueMessage(SwiftMimeMessage $message, MembersNotificationEvent $event, EventDispatcherInterface $dispatcher)
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

    /**
     * Generate the HTML for the verification email.
     *
     * @param MembersProfileEvent $event
     *
     * @return string
     */
    private function getRegisterHtml(MembersProfileEvent $event)
    {
        $meta = $event->getMetaEntityNames();
        $query = http_build_query(['code' => $meta[AccountVerification::KEY_NAME]]);
        $context = [
            'name'   => $event->getAccount()->getDisplayname(),
            'email'  => $event->getAccount()->getEmail(),
            'link'   => sprintf('%s/%s/profile/verify?%s', $this->siteUrl, $this->config->getUrlMembers(), $query),
            'member' => $event->getAccount(),
        ];
        $mailHtml = $this->twig->render($this->config->getTemplate('verification', 'body'), $context);

        return $mailHtml;
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
}
