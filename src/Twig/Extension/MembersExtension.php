<?php

namespace Bolt\Extension\BoltAuth\Auth\Twig\Extension;

use Twig_Extension as Extension;
use Twig_SimpleFunction as SimpleFunction;

/**
 * Twig extension definition.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $safe = ['is_safe' => ['html'], 'is_safe_callback' => true];
        $env  = ['needs_environment' => true];

        return [
            new SimpleFunction('is_auth',                     [AuthRuntime::class, 'isAuth'],        $safe),
            new SimpleFunction('auth',                        [AuthRuntime::class, 'getAuth'],       $safe),
            new SimpleFunction('auth_meta',                   [AuthRuntime::class, 'getAuthMeta'],   $safe),
            new SimpleFunction('auth_oauth',                  [AuthRuntime::class, 'getAuthOauth'],  $safe),
            new SimpleFunction('auth_has_role',               [AuthRuntime::class, 'hasRole'],         $safe),
            new SimpleFunction('auth_providers',              [AuthRuntime::class, 'getProviders'],    $safe),
            new SimpleFunction('auth_auth_switcher',         [AuthRuntime::class, 'renderSwitcher'],  $safe + $env),
            new SimpleFunction('auth_auth_associate',        [AuthRuntime::class, 'renderAssociate'], $safe + $env),
            new SimpleFunction('auth_auth_login',            [AuthRuntime::class, 'renderLogin'],     $safe + $env),
            new SimpleFunction('auth_auth_logout',           [AuthRuntime::class, 'renderLogout'],    $safe + $env),
            new SimpleFunction('auth_link_auth_login',       [AuthRuntime::class, 'getLinkLogin'],    $safe),
            new SimpleFunction('auth_link_auth_logout',      [AuthRuntime::class, 'getLinkLogout'],   $safe),
            new SimpleFunction('auth_link_auth_reset',       [AuthRuntime::class, 'getLinkReset'],    $safe),
            new SimpleFunction('auth_link_profile_edit',     [AuthRuntime::class, 'getLinkEdit'],     $safe),
            new SimpleFunction('auth_link_profile_register', [AuthRuntime::class, 'getLinkRegister'], $safe),
            new SimpleFunction('auth_profile_edit',          [AuthRuntime::class, 'renderEdit'],      $safe + $env),
            new SimpleFunction('auth_profile_register',      [AuthRuntime::class, 'renderRegister'],  $safe + $env),
        ];
    }
}
