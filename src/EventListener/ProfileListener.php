<?php

namespace Bolt\Extension\Bolt\Members\EventListener;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Swift_Mailer as SwiftMailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig_Environment as TwigEnvironment;

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
     * @param Config          $config
     * @param TwigEnvironment $twig
     * @param SwiftMailer     $mailer
     * @param string          $siteUrl
     */
    public function __construct(Config $config, TwigEnvironment $twig, SwiftMailer $mailer, $siteUrl)
    {
        $this->config = $config;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->siteUrl = $siteUrl;
    }

    public function onProfileRegister(MembersProfileEvent $event)
    {
        $registration = $this->config->getRegistration();
        $from = [(string) $registration['verify_email'] => $registration['verify_name']];
        $email = [$event->getAccount()->getEmail() =>$event->getAccount()->getDisplayname()];
        $subject = $this->twig->render($this->config->getTemplates('verification', 'subject'));
        $mailHtml = $this->getRegisterHtml($event);

        $message = $this->mailer
            ->createMessage('message')
            ->setSubject($subject)
            ->setFrom($from)
            ->setReplyTo($from)
            ->setTo($email)
            ->setBody(strip_tags($mailHtml))
            ->addPart($mailHtml, 'text/html')
        ;
        $failedRecipients = [];

        $this->mailer->send($message, $failedRecipients);
    }

    /**
     * @return string
     */
    private function getRegisterHtml(MembersProfileEvent $event)
    {
        $meta = $event->getMetaFields();
        $context = [
            'name'  => $event->getAccount()->getDisplayname(),
            'email' => $event->getAccount()->getEmail(),
            'link'  => sprintf('%s%s/profile/verify?%s', $this->siteUrl, $this->config->getUrlMembers(),$meta['account-verification-key']),
        ];
        $mailHtml = $this->twig->render($this->config->getTemplates('verification', 'body'), $context);

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
