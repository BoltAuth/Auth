<?php

namespace Bolt\Extension\BoltAuth\Auth\Controller;

use Bolt\Extension\BoltAuth\Auth\AccessControl\Session;
use Bolt\Extension\BoltAuth\Auth\Config\Config;
use Bolt\Extension\BoltAuth\Auth\Feedback;
use Bolt\Extension\BoltAuth\Auth\Form;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Client\ProviderManager;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Handler\HandlerInterface;
use Bolt\Extension\BoltAuth\Auth\Storage\FormEntityHandler;
use Bolt\Extension\BoltAuth\Auth\Storage\Records;
use League\OAuth2\Client\Provider\AbstractProvider;
use Silex\Application;

/**
 * Auth services trait.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
trait AuthServicesTrait
{
    /**
      * @return Config
      */
     protected function getAuthConfig()
     {
         return $this->getContainer()['auth.config'];
     }

    /**
     * @return Feedback
     */
    protected function getAuthFeedback()
    {
        return $this->getContainer()['auth.feedback'];
    }
    /**
     * @return Form\Manager
     */
    protected function getAuthFormsManager()
    {
        return $this->getContainer()['auth.forms.manager'];
    }

    /**
     * @return HandlerInterface
     */
    protected function getAuthOauthHandler()
    {
        return $this->getContainer()['auth.oauth.handler'];
    }

    /**
     * @return AbstractProvider
     */
    protected function getAuthOauthProvider()
    {
        return $this->getContainer()['auth.oauth.provider'];
    }

    /**
     * @return ProviderManager
     */
    protected function getAuthOauthProviderManager()
    {
        return $this->getContainer()['auth.oauth.provider.manager'];
    }

    /**
     * @return Records
     */
    protected function getAuthRecords()
    {
        return $this->getContainer()['auth.records'];
    }

    /**
     * @return FormEntityHandler
     */
    protected function getAuthRecordsProfile()
    {
        return $this->getContainer()['auth.records.profile'];
    }

    /**
     * @return Session
     */
    protected function getAuthSession()
    {
        return $this->getContainer()['auth.session'];
    }

    /**
     * @internal Should only be used by traits.
     *
     * @return Application
     */
    abstract protected function getContainer();
}
