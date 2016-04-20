<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
use Bolt\Extension\Bolt\Members\Storage;
use Carbon\Carbon;

/**
 * Base profile editing/registration form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class BaseProfile extends AbstractForm
{
    /** @var string */
    protected $guid;

    /**
     * Create a local OAuth account record.
     *
     * @param Storage\Records $records
     *
     * @return Storage\Entity\Oauth
     */
    protected function createLocalOauthAccount(Storage\Records $records)
    {
        $encryptedPassword = password_hash($this->form->get('password')->getData(), PASSWORD_BCRYPT);
        $oauth = $records->createOauth($this->guid, $this->guid, true);
        $oauth->setPassword($encryptedPassword);

        $records->saveOauth($oauth);

        return $oauth;
    }

    /**
     * Create a 'local' provider record.
     *
     * @param Storage\Records $records
     *
     * @return Storage\Entity\Provider
     */
    protected function createLocalProvider(Storage\Records $records)
    {
        $provider = $records->createProvision($this->guid, 'local', $this->guid);

        return $provider;
    }
}
