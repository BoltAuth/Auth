<?php

namespace Bolt\Extension\Bolt\Members\Handler;

use Bolt\Extension\Bolt\Members\AccessControl\Validator\AccountVerification;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationEvent;
use Bolt\Extension\Bolt\Members\Event\MembersNotificationFailureEvent;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Swift_Mime_Message as SwiftMimeMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Profile handler base class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileRegister extends AbstractProfileHandler
{
    /**
     * Profile registration event.
     *
     * @param MembersProfileEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function handle(MembersProfileEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $from = [$this->config->getNotificationEmail() => $this->config->getNotificationName()];
        $email = [$event->getAccount()->getEmail() => $event->getAccount()->getDisplayname()];

        /** @var SwiftMimeMessage $message */
        $message = $this->mailer->createMessage('message');

        try {
            $message
                ->setTo($email)
                ->setFrom($from)
                ->setReplyTo($from)
                ->setSubject($this->getSubject($event))
            ;
        } catch (\Swift_RfcComplianceException $e) {
            // Dispatch an event
            $event = new MembersNotificationFailureEvent($message, $e);
            $dispatcher->dispatch(MembersEvents::MEMBER_NOTIFICATION_FAILURE, $event);

            return;
        }

        $this->setBody($message, $event);
        $event = new MembersNotificationEvent($message);
        $this->queueMessage($message, $event, $dispatcher);
    }

    /**
     * Generate the HTML and/or text for the verification email.
     *
     * @param MembersProfileEvent $event
     */
    private function setBody(SwiftMimeMessage $message, MembersProfileEvent $event)
    {
        $meta = $event->getMetaEntityNames();
        $link = $this->urlGenerator->generate(
            'membersProfileVerify',
            ['code' => $meta[AccountVerification::KEY_NAME]],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $context = [
            'name'   => $event->getAccount()->getDisplayname(),
            'email'  => $event->getAccount()->getEmail(),
            'link'   => $link,
            'member' => $event->getAccount(),
        ];

        $template = $this->config->getTemplate('verification', 'text');
        $bodyText = $this->twig->render($template, $context);
        $message->setBody($bodyText);

        if ($this->config->getNotificationEmailFormat() !== 'text') {
            $template = $this->config->getTemplate('verification', 'html');
            $bodyHtml = $this->twig->render($template, $context);
            /** @var \Swift_Message $message */
            $message->addPart($bodyHtml, 'text/html');
        }
    }

    /**
     * Generate the subject line for the verification email.
     *
     * @param MembersProfileEvent $event
     *
     * @return string
     */
    private function getSubject(MembersProfileEvent $event)
    {
        $template = $this->config->getTemplate('verification', 'subject');
        $context = [
            'member' => $event->getAccount(),
        ];

        return $this->twig->render($template, $context);
    }
}
