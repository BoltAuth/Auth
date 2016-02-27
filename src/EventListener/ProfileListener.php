<?php

namespace Bolt\Extension\Bolt\Members\EventListener;

use Bolt\Extension\Bolt\Members\AccessControl\Validator\AccountVerification;
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
        $from = [$this->config->getNotificationEmail() => $this->config->getNotificationName()];
        $email = [$event->getAccount()->getEmail() => $event->getAccount()->getDisplayname()];
        $subject = $this->twig->render($this->config->getTemplates('verification', 'subject'));
        $mailHtml = $this->getRegisterHtml($event);

        try {
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
        } catch (\Swift_RfcComplianceException $e) {
            // Email not configured
        }
    }

    /**
     * @return string
     */
    private function getRegisterHtml(MembersProfileEvent $event)
    {
        $meta = $event->getMetaFieldNames();
        $query = http_build_query(['code' => $meta[AccountVerification::KEY_NAME]]);
        $context = [
            'name'  => $event->getAccount()->getDisplayname(),
            'email' => $event->getAccount()->getEmail(),
            'link'  => sprintf('%s%s/profile/verify?%s', $this->siteUrl, $this->config->getUrlMembers(), $query),
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
