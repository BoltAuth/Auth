<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Form;
use Bolt\Extension\Bolt\Members\Oauth2\Client\ProviderManager;
use Bolt\Extension\Bolt\Members\Oauth2\Handler\HandlerInterface;
use Bolt\Extension\Bolt\Members\Storage\FormEntityHandler;
use Bolt\Extension\Bolt\Members\Storage\Records;
use League\OAuth2\Client\Provider\AbstractProvider;
use Silex\Application;

/**
 * Members services trait.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
trait MembersServicesTrait
{
    /**
      * @return Config
      */
     protected function getMembersConfig()
     {
         return $this->getContainer()['members.config'];
     }

    /**
     * @return Feedback
     */
    protected function getMembersFeedback()
    {
        return $this->getContainer()['members.feedback'];
    }
    /**
     * @return Form\Manager
     */
    protected function getMembersFormsManager()
    {
        return $this->getContainer()['members.forms.manager'];
    }

    /**
     * @return HandlerInterface
     */
    protected function getMembersOauthHandler()
    {
        return $this->getContainer()['members.oauth.handler'];
    }

    /**
     * @return AbstractProvider
     */
    protected function getMembersOauthProvider()
    {
        return $this->getContainer()['members.oauth.provider'];
    }

    /**
     * @return ProviderManager
     */
    protected function getMembersOauthProviderManager()
    {
        return $this->getContainer()['members.oauth.provider.manager'];
    }

    /**
     * @return Records
     */
    protected function getMembersRecords()
    {
        return $this->getContainer()['members.records'];
    }

    /**
     * @return FormEntityHandler
     */
    protected function getMembersRecordsProfile()
    {
        return $this->getContainer()['members.records.profile'];
    }

    /**
     * @return Session
     */
    protected function getMembersSession()
    {
        return $this->getContainer()['members.session'];
    }

    /**
     * @return Application
     */
    abstract protected function getContainer();
}
