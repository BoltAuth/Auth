<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Password reset form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfilePasswordResetForm extends AbstractForm
{
    /** @var string */
    protected $guid;

    /**
     * @param string $guid
     *
     * @return ProfileEditForm
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function saveForm(Storage\Records $records, EventDispatcherInterface $eventDispatcher)
    {
        /** @var Storage\Entity\Oauth $oauth */
        $oauth = $records->getOauthByGuid($this->guid);
        if ($oauth !== false) {
            $encryptedPassword = password_hash($this->form->get('password')->getData(), PASSWORD_BCRYPT);
            $oauth->setPassword($encryptedPassword);
            $records->saveOauth($oauth);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getData(Storage\Records $records)
    {
        return [
            'csrf_protection' => true,
        ];
    }
}
