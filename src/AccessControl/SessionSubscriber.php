<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for Session to maintain lazy loading.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class SessionSubscriber implements EventSubscriberInterface
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
            KernelEvents::RESPONSE => [
                ['persistData'],
                ['saveRedirects'],
            ],
            KernelEvents::REQUEST => [
                ['loadRedirects'],
            ],
        ];
    }

    public function persistData()
    {
        $this->getMembersSession()->persistData();
    }

    public function saveRedirects()
    {
        $this->getMembersSession()->saveRedirects();
    }

    public function loadRedirects()
    {
        $this->getMembersSession()->loadRedirects();
    }

    /**
     * @return Session
     */
    private function getMembersSession()
    {
        return $this->app['members.session'];
    }
}
