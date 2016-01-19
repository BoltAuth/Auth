<?php

namespace Bolt\Extension\Bolt\Members;

use Silex\Application;

/**
 * Members administration functions
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Admin extends Members
{
    /** @var Application */
    private $app;
    /** @var array */
    private $config;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param array       $config
     */
    public function __construct(Application $app, array $config)
    {
        parent::__construct($app, $config);

        $this->app = $app;
        $this->config = $config;
    }

    /**
     * @return Members
     */
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
     *
     * @return bool
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
     *
     * @return bool
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
