<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\AbstractExtension;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Membership management extension for Bolt
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersExtension extends AbstractExtension implements ServiceProviderInterface, EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public function register(Application $app)
    {
    }

    /**
     * @inheritDoc
     */
    public function boot(Application $app)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
    }

    /**
     * @inheritDoc
     */
    public function getServiceProviders()
    {
    }
}
