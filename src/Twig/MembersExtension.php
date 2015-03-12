<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Bolt\Extension\Bolt\Members\Extension;

/**
 * Twig functions
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
class MembersExtension extends \Twig_Extension
{
    /** @var Application */
    private $app;

    /** @var array */
    private $config;

    /** @var \Bolt\Extension\Bolt\Members\Members */
    private $members;

    /** @var \Twig_Environment */
    private $twig = null;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twig = $environment;
    }

    /**
     * Return the name of the extension
     */
    public function getName()
    {
        return 'members.twig';
    }

    /**
     * The functions we add
     */
    public function getFunctions()
    {
        return array(
            'member'     => new \Twig_Function_Method($this, 'member'),
            'memberauth' => new \Twig_Function_Method($this, 'memberAuth'),
            'hasrole'    => new \Twig_Function_Method($this, 'hasRole')
        );
    }

    /**
     * Return Twig suitable array for a member, or current session
     *
     * @param integer $id   [Optional] ID of member to look up
     * @param boolean $meta [Optional] Return user meta
     *
     * @return \Twig_Markup
     */
    public function member($id = false, $meta = false)
    {
        $member = $this->app['members']->getMember('id', $id);

        if ($meta) {
            $member['meta'] = $this->app['members']->getMemberMeta($id);
        }

        return new \Twig_Markup($member, 'UTF-8');
    }

    /**
     * Test if a user has a valid ClientLogin session AND is a valid member
     *
     * @return boolean|integer Member ID, or false
     */
    public function memberAuth()
    {
        return $this->app['members']->isAuth();
    }

    /**
     * Test a member record to see if they have a specific role
     *
     * @param string $role
     * @param string $id
     *
     * @return boolean
     */
    public function hasRole($role, $id = false)
    {
        if ($id === false) {
            $member = $this->app['members']->isAuth();

            if ($member) {
                $id = $member;
            }
        }

        if ($id) {
            return $this->app['members']->hasRole('id', $id, $role);
        }

        return false;
    }
}
