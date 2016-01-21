<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Silex\Application;

/**
 * Twig functions
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersExtension
{
    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Test a member record to see if they have a specific role
     *
     * @param string  $role
     * @param integer $id
     *
     * @return boolean
     */
    public function hasRole($role, $id = null)
    {
        if ($id === false) {
            $member = $this->getMembers()->isMember();

            if ($member) {
                $id = $member;
            }
        }

        if ($id) {
            return $this->getMembers()->hasRole('id', $id, $role);
        }

        return false;
    }

    /**
     * @return \Bolt\Extension\Bolt\Members\Members
     */
    private function getMembers()
    {
        return $this->app['members'];
    }
}
