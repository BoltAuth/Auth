<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\Bolt\ClientLogin\Session;
use Silex;
use Silex\Application;

/**
 * Member interface class
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
class Members
{
    /**
     * @var Silex\Application
     */
    private $app;

    /**
     * Extension config array
     *
     * @var array
     */
    private $config;

    /**
     * @var Records
     */
    private $records;

    /**
     * @var array
     */
    private $roles = array();

    /**
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
        $this->records = new Records($this->app);
    }

    /**
     * Roles currently set
     *
     * @return array
     */
    public function getAvailableRoles()
    {
        return $this->roles;
    }

    /**
     * Add a role
     *
     * @param string $role The internal name for the role
     * @param string $name The user friendly name for the role
     */
    public function addAvailableRole($role, $name = '')
    {
        if ($name == '') {
            $name = $role;
        }

        try {
            $this->roles[$role] = $name;
        } catch (Exception $e) {
        }
    }

    /**
     * Get a member record
     *
     * @param string $field The user field to lookup the user by (id, username or email)
     * @param string $value Lookup value
     *
     * @return array|boolean
     */
    public function getMember($field, $value)
    {
        if (! empty($field) && ! empty($value)) {
            $record = $this->records->getMember($field, $value);
            if ($record) {
                return $record;
            }
        }

        return false;
    }

    /**
     * Get a member's meta records
     *
     * @param integer $id   The user's ID
     * @param string  $meta Optional meta value to limit to
     *
     * @return array|boolean
     */
    public function getMemberMeta($id, $meta = false)
    {
        $records = $this->records->getMemberMeta($id, $meta);

        if ($records) {
            return $records;
        }

        return false;
    }

    /**
     * Update/insert a member record in the database
     *
     * @param int   $userid
     * @param array $values
     *
     * @return boolean
     */
    public function updateMember($userid, $values)
    {
        return $this->records->updateMember($userid, $values);
    }

    /**
     * Add/update a member's meta record
     *
     * @param int    $userid
     * @param string $meta
     * @param string $value
     *
     * @return boolean
     */
    public function updateMemberMeta($userid, $meta, $value)
    {
        return $this->records->updateMemberMeta($userid, $meta, $value);
    }

    /**
     * Test if a user has a valid ClientLogin session AND is a valid member
     *
     * @return boolean|integer Member ID, or false
     */
    public function isAuth()
    {
        $auth = new Authenticate($this->app);

        return $auth->isAuth();
    }

    /**
     * Test a member record to see if they have a specific role
     *
     * @param string $field The user field to lookup the user by (id, username or email)
     * @param string $value Lookup value
     * @param string $role  The role to test
     *
     * @return boolean
     */
    public function hasRole($field, $value, $role)
    {
        $member = $this->getMember($field, $value);

        if (is_array($member['roles']) && in_array($role, $member['roles'])) {
            return true;
        }

        return false;
    }

    /**
     * Get a set of members record from the database
     *
     * @return boolean|array
     */
    protected function getMembers()
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
}
