<?php

namespace Bolt\Extension\Bolt\Members\Twig\Extension;

use Twig_Extension as Extension;
use Twig_SimpleFunction as SimpleFunction;

/**
 * Twig extension definition.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $safe = ['is_safe' => ['html'], 'is_safe_callback' => true];
        $env  = ['needs_environment' => true];

        return [
            new SimpleFunction('is_member',                     [MembersRuntime::class, 'isMember'],        $safe),
            new SimpleFunction('member',                        [MembersRuntime::class, 'getMember'],       $safe),
            new SimpleFunction('member_meta',                   [MembersRuntime::class, 'getMemberMeta'],   $safe),
            new SimpleFunction('member_oauth',                  [MembersRuntime::class, 'getMemberOauth'],  $safe),
            new SimpleFunction('member_has_role',               [MembersRuntime::class, 'hasRole'],         $safe),
            new SimpleFunction('member_providers',              [MembersRuntime::class, 'getProviders'],    $safe),
            new SimpleFunction('members_auth_switcher',         [MembersRuntime::class, 'renderSwitcher'],  $safe + $env),
            new SimpleFunction('members_auth_associate',        [MembersRuntime::class, 'renderAssociate'], $safe + $env),
            new SimpleFunction('members_auth_login',            [MembersRuntime::class, 'renderLogin'],     $safe + $env),
            new SimpleFunction('members_auth_logout',           [MembersRuntime::class, 'renderLogout'],    $safe + $env),
            new SimpleFunction('members_link_auth_login',       [MembersRuntime::class, 'getLinkLogin'],    $safe),
            new SimpleFunction('members_link_auth_logout',      [MembersRuntime::class, 'getLinkLogout'],   $safe),
            new SimpleFunction('members_link_auth_reset',       [MembersRuntime::class, 'getLinkReset'],    $safe),
            new SimpleFunction('members_link_profile_edit',     [MembersRuntime::class, 'getLinkEdit'],     $safe),
            new SimpleFunction('members_link_profile_register', [MembersRuntime::class, 'getLinkRegister'], $safe),
            new SimpleFunction('members_profile_edit',          [MembersRuntime::class, 'renderEdit'],      $safe + $env),
            new SimpleFunction('members_profile_register',      [MembersRuntime::class, 'renderRegister'],  $safe + $env),
        ];
    }
}
