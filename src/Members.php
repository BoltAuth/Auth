<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;
use Bolt\Extension\Bolt\ClientLogin\Session;
use Bolt\Extension\Bolt\ClientLogin\ClientRecords;

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

    public function __construct(Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
        $this->records = new Records($this->app);
    }

    /**
     * Get a member record
     *
     * @param  string        $field The user field to lookup the user by (id, username or email)
     * @param  string        $value Lookup value
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
     * @param  integer       $id   The user's ID
     * @param  string        $meta Optional meta value to limit to
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
     * Add/update a member's meta record
     *
     * @param  int     $userid
     * @param  string  $meta
     * @param  string  $value
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
        // First check for ClientLogin auth
        $session = new Session($this->app);
        if (! $session->doCheckLogin()) {
            return false;
        }

        // Get their ClientLogin records
        $records = new ClientRecords($this->app);
        $record = $records->getUserProfileBySession($session->token);
        if (! $record) {
            return false;
        }

        // Look them up internally
        $key = 'clientlogin_id_' . strtolower($records->user['provider']);
        $record = $this->records->getMetaRecords($key, $records->user['identifier'], true);
        if ($record) {
            return $record['userid'];
        }

        return false;
    }

}
