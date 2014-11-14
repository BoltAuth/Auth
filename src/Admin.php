<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;
use Bolt\Helpers\String;
use Bolt\Translation\Translator as Trans;

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
class Admin
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Records
     */
    private $records;

    public function __construct(Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
        $this->records = new Records($this->app);
    }

    /**
     * Get a set of members record from the database
     *
     * @return boolean|array
     */
    public function getMembers()
    {
        $query = "SELECT * FROM " . $this->records->getTableName();

        $records = $this->app['db']->fetchAll($query);

        if (empty($records)) {
            return false;
        } else {
            foreach ($records as $key => $record) {
                if (isset($record['roles'])) {
                    $records[$key]['roles'] = json_decode($record['roles'], true);
                }
            }

            return $records;
        }
    }

    /**
     * Return the roles added by extensions to our service
     *
     * @return array
     */
    public function getSubscribedRoles()
    {
        return $this->app['members.events.roles']->getRoles();
    }

    /**
     * Enable a member
     *
     * @param integer $id
     */
    public function memberEnable($id)
    {
        //
    }

    /**
     * Disable a member
     *
     * @param integer $id
     */
    public function memberDisable($id)
    {
        //
    }

    /**
     * Add roles for a member
     *
     * @param integer $id
     * @param string|array $roles
     */
    public function memberRolesAdd($id, $roles)
    {
        //
    }

    /**
     * Remove roles for a member
     *
     * @param integer $id
     * @param string|array $roles
     */
    public function memberRolesRemove($id, $roles)
    {
        //
    }
}
