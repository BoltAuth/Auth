<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Bolt\Extension\Bolt\Members\Extension;
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
        return [
            'member'     => new \Twig_Function_Method($this, 'member'),
            'memberauth' => new \Twig_Function_Method($this, 'memberAuth'),
            'hasrole'    => new \Twig_Function_Method($this, 'hasRole'),
        ];
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
