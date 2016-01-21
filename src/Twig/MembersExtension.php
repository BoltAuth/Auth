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
     * Return Twig suitable array for a member, or current session
     *
     * @param integer $id   [Optional] ID of member to look up
     * @param boolean $meta [Optional] Return user meta
     *
     * @return \Twig_Markup
     */
    public function member($id = null, $meta = false)
    {
        $member = $this->getMembers()->getMember('id', $id);

        if ($meta) {
            $member['meta'] = $this->getMembers()->getMemberMeta($id);
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
        return $this->getMembers()->isAuth();
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
            $member = $this->getMembers()->isAuth();

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
