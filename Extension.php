<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\Bolt\ClientLogin\ClientLoginEvent;
use Bolt\Translation\Translator as Trans;

/**
 * Membership management extension for Bolt
 *
 * Copyright (C) 2014  Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class Extension extends \Bolt\BaseExtension
{
    /** @var string Extension name */
    const NAME = 'Members';

    /** @var boolean */
    private $isAdmin;

    /**
     * Extension's container
     *
     * @var string
     */
    const CONTAINER = 'extensions.Members';

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize()
    {
        /*
         * Provider
         */
        $this->app->register(new Provider\MembersServiceProvider());

        /*
         * Backend
         */
        if ($this->app['config']->getWhichEnd() == 'backend') {
            // Check & create database tables if required
            $records = new Records($this->app);
            $records->dbCheck();

            // Create the admin page
            $this->adminMenu();
        }

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() == 'frontend') {
            // Register ourselves as a service
            $this->app->register(new Provider\MembersServiceProvider($this->app));

            // Twig functions
            $this->app['twig']->addExtension(new Twig\MembersExtension($this->app));
        }

        /*
         * Controllers
         */
        $path = $this->app['config']->get('general/branding/path') . '/extensions/members';
        $this->app->mount($path, new Controller\MembersAdminController());
        $this->app->mount('/' . $this->config['basepath'], new Controller\MembersController());

        /*
         * Hooks
         */
        $this->app['dispatcher']->addListener('clientlogin.Login',  array($this, 'loginCallback'));
        $this->app['dispatcher']->addListener('clientlogin.Logout', array($this, 'logoutCallback'));
    }

    /**
     * Hook for ClientLogin login events
     *
     * @param ClientLoginEvent $event
     */
    public function loginCallback(ClientLoginEvent $event)
    {
        $auth = new Authenticate($this->app);
        $auth->login($event);
    }

    /**
     * Hook for ClientLogin logout events
     *
     * @param ClientLoginEvent $event
     */
    public function logoutCallback(ClientLoginEvent $event)
    {
        $auth = new Authenticate($this->app);
        $auth->logout($event);
    }

    /**
     * Determine if the user has admin rights to the page
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if (is_null($this->isAdmin)) {
            // check if user has allowed role(s)
            $user    = $this->app['users']->getCurrentUser();
            $userid  = $user['id'];

            $this->isAdmin = false;

            foreach ($this->config['admin_roles'] as $role) {
                if ($this->app['users']->hasRole($userid, $role)) {
                    $this->isAdmin = true;
                    break;
                }
            }
        }

        return $this->isAdmin;
    }

    /**
     * Conditionally create the admin menu if the user has a valid role
     */
    private function adminMenu()
    {
        if ($this->isAdmin()) {
            $path = $this->app['resources']->getUrl('bolt') . 'extensions/members';
            $this->app[Extension::CONTAINER]->addMenuOption(Trans::__('Members'), $path, 'fa:users');
        }
    }

    private function checkAuthorized()
    {
        // check if user has allowed role(s)
        $user    = $this->app['users']->getCurrentUser();
        $userid  = $user['id'];

        foreach ($this->config['admin_roles'] as $role) {
            if ($this->app['users']->hasRole($userid, $role)) {
                $this->isAdmin = true;
                break;
            }
        }
    }

    /**
     * Default config options
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return array(
            'basepath'     => 'members',
            'templates'    => array(
                'parent'        => 'members.twig',
                'register'      => 'members_register.twig',
                'profile_edit'  => 'members_profile_edit.twig',
                'profile_view'  => 'members_profile_view.twig'
            ),
            'registration' => true,
            'csrf'         => true,
            'admin_roles'  => array('root', 'admin', 'developer', 'chief-editor')
        );
    }
}
