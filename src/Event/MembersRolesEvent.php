<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt;
use Symfony\Component\EventDispatcher\Event;

/**
 * Members role addition class
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
class MembersRolesEvent extends Event
{
    /**
     * @var array
     */
    private $roles = array();

    public function __construct()
    {
    }

    /**
     * Roles currently set
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add a role
     *
     * @param string $role
     */
    public function addRole($role)
    {
        try {
            array_push($this->roles, (string) $role);
        } catch (Exception $e) {
        }
    }
}
