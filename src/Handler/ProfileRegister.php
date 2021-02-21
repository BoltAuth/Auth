<?php

namespace Bolt\Extension\BoltAuth\Auth\Handler;

use Bolt\Extension\BoltAuth\Auth\AccessControl\Validator\AccountVerification;
use Bolt\Extension\BoltAuth\Auth\Event\AuthEvents;
use Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationEvent;
use Bolt\Extension\BoltAuth\Auth\Event\AuthNotificationFailureEvent;
use Bolt\Extension\BoltAuth\Auth\Event\AuthProfileEvent;
use Swift_Mime_Message as SwiftMimeMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
class ProfileRegister extends AbstractProfileHandler
{
    /**
     * Profile registration event.
     *
     * @param AuthProfileEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function handle(AuthProfileEvent $event, $eventName, EventDispatcherInterface $dispatcher)
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
            $event = new AuthNotificationFailureEvent($message, $e);
            $dispatcher->dispatch(AuthEvents::AUTH_NOTIFICATION_FAILURE, $event);

            return;
        }

        $this->setBody($message, $event);
        $event = new AuthNotificationEvent($message);
        $this->queueMessage($message, $event, $dispatcher);
    }

    /**
     * Generate the HTML and/or text for the verification email.
     *
     * @param AuthProfileEvent $event
     */
    private function setBody(SwiftMimeMessage $message, AuthProfileEvent $event)
    {
        $meta = $event->getMetaEntityNames();
        $link = $this->urlGenerator->generate(
            'authProfileVerify',
            ['code' => $meta[AccountVerification::KEY_NAME]],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $context = [
            'name'   => $event->getAccount()->getDisplayname(),
            'email'  => $event->getAccount()->getEmail(),
            'link'   => $link,
            'auth' => $event->getAccount(),
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
     * @param AuthProfileEvent $event
     *
     * @return string
     */
    private function getSubject(AuthProfileEvent $event)
    {
        $template = $this->config->getTemplate('verification', 'subject');
        $context = [
            'auth' => $event->getAccount(),
        ];

        return $this->twig->render($template, $context);
    }
}
