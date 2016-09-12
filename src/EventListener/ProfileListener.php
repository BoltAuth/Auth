<?php

namespace Bolt\Extension\Bolt\Members\EventListener;

use Bolt\Extension\Bolt\Members\AccessControl\Validator\AccountVerification;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationFailureEvent;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Swift_Mailer as SwiftMailer;
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
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param TwigEnvironment          $twig
     * @param SwiftMailer              $mailer
     * @param EventDispatcherInterface $dispatcher
     * @param                          $siteUrl
     */
    public function __construct(Config $config, TwigEnvironment $twig, SwiftMailer $mailer, EventDispatcherInterface $dispatcher, $siteUrl)
    {
        $this->config = $config;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
        $this->siteUrl = $siteUrl;
    }

    /**
     * Profile registration event.
     *
     * @param MembersProfileEvent $event
     */
    public function onProfileRegister(MembersProfileEvent $event)
    {
        $from = [$this->config->getNotificationEmail() => $this->config->getNotificationName()];
        $email = [$event->getAccount()->getEmail() => $event->getAccount()->getDisplayname()];
        $subject = $this->twig->render($this->config->getTemplate('verification', 'subject'));
        $mailHtml = $this->getRegisterHtml($event);

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
            $failedRecipients = [];

            // Dispatch an event
            $event = new MembersNotificationEvent($message);
            $this->dispatcher->dispatch(MembersEvents::MEMBER_NOTIFICATION_PRE_SEND, $event);

            $this->mailer->send($message, $failedRecipients);
        } catch (\Swift_RfcComplianceException $e) {
            // Dispatch an event
            $event = new MembersNotificationFailureEvent($message, $e);
            $this->dispatcher->dispatch(MembersEvents::MEMBER_NOTIFICATION_FAILURE, $event);
        }
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
            'name'  => $event->getAccount()->getDisplayname(),
            'email' => $event->getAccount()->getEmail(),
            'link'  => sprintf('%s/%s/profile/verify?%s', $this->siteUrl, $this->config->getUrlMembers(), $query),
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
        ];
    }
}
