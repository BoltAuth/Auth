<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Helpers\String;
use Silex\Application;

/**
 * BoltBB administration functions
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
class Admin extends Members
{
    /** @var Application */
    private $app;
    /** @var array */
    private $config;
    /** @var Records */
    private $records;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param array       $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function getMembers()
    {
        return $this->app['members']->getMembers();
    }

    /**
     * Return the roles added by extensions to our service
     *
     * @return array
     */
    public function getSubscribedRoles()
    {
        return $this->app['members']->getRoles();
    }

    /**
     * Enable a member
     *
     * @param integer $id
     */
    public function memberEnable($id)
    {
        return $this->app['members']->updateMember($id, ['enabled' => 1]);
    }

    /**
     * Disable a member
     *
     * @param integer $id
     */
    public function memberDisable($id)
    {
        return $this->app['members']->updateMember($id, ['enabled' => 0]);
    }

    /**
     * Add roles for a member
     *
     * @param integer      $id
     * @param string|array $roles
     */
    public function memberRolesAdd($id, $roles)
    {
        if (!$member = $this->app['members']->getMember('id', $id)) {
            return false;
        }

        $member = $this->app['members']->getMember('id', $id);
        $currentRoles = isset($member['roles']) && !empty($member['roles']) ? $member['roles'] : [];
        $currentRoles = json_encode(array_unique(array_merge((array) $currentRoles, (array) $roles)));
        array_map('trim', $currentRoles);

        return $this->app['members']->updateMember($id, ['roles' => $currentRoles]);
    }

    /**
     * Remove roles for a member
     *
     * @param integer      $id
     * @param string|array $roles
     */
    public function memberRolesRemove($id, $roles)
    {
        if (!$member = $this->app['members']->getMember('id', $id)) {
            return false;
        }

        $member = $this->app['members']->getMember('id', $id);
        $roles = (array) $roles;

        foreach ($roles as $role) {
            foreach (array_keys($member['roles'], $role, true) as $key) {
                unset($member['roles'][$key]);
            }
        }

        return $this->app['members']->updateMember($id, ['roles' => json_encode($member['roles'])]);
    }
}
