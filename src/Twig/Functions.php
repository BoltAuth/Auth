<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Bolt\Extension\Bolt\Members\Config\Config;
use Twig_Markup;

/**
 * Twig functions.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Functions
{
    /** @var Config */
    private $config;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Check if the current session is a logged-in member.
     *
     * @return bool
     */
    public function isMember()
    {
        return true;
    }

    /**
     * Check if the current logged-in session has a member role.
     *
     * @return bool
     */
    public function hasRole()
    {
        return true;
    }

    /**
     * Display login/logout button(s) depending on status.
     *
     * @param bool $redirect
     *
     * @return Twig_Markup
     */
    public function renderSwitcher($redirect = false)
    {
        return new Twig_Markup('', 'UTF-8');
    }

    /**
     * Display logout button(s).
     *
     * @param bool $redirect
     *
     * @return Twig_Markup
     */
    public function renderLogin($redirect = false)
    {
        return new Twig_Markup('', 'UTF-8');
    }

    /**
     * Display logout button.
     *
     * @param bool $redirect
     *
     * @return Twig_Markup
     */
    public function renderLogout($redirect = false)
    {
        return new Twig_Markup('', 'UTF-8');
    }
}
