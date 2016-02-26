<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Profile form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileEditForm extends BaseProfile
{
    /** @var Type\ProfileEditType */
    protected $type;
    /** @var Entity\ProfileEdit */
    protected $entity;
    /** @var Storage\Entity\Account */
    protected $account;
    /** @var Storage\Entity\AccountMeta */
    protected $accountMeta;

    /**
     * @return Storage\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Storage\Entity\Account $account
     *
     * @return ProfileEditForm
     */
    public function setAccount(Storage\Entity\Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Storage\Entity\AccountMeta
     */
    public function getAccountMeta()
    {
        return $this->accountMeta;
    }

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
     * {@inheritdoc}
     */
    public function saveForm(Storage\Records $records, EventDispatcherInterface $eventDispatcher)
    {
        if ($this->guid === null) {
            throw new \RuntimeException('GUID not set.');
        }

        $this->account->setDisplayname($this->form->get('displayname')->getData());
        $this->account->setEmail($this->form->get('email')->getData());

        // Dispatch the account profile pre-save event
        $event = new MembersProfileEvent($this->account);
        $eventDispatcher->dispatch(MembersEvents::MEMBER_PROFILE_PRE_SAVE, $event);

        $records->saveAccount($this->account);

        if ($this->form->get('plainPassword')->getData() !== null) {
            $encryptedPassword = password_hash($this->form->get('plainPassword')->getData(), PASSWORD_BCRYPT);
            $oauth = $this->getOauth($records);
            $oauth->setPassword($encryptedPassword);
            $records->saveOauth($oauth);
        }

        // Save any defined meta fields
        foreach ($event->getMetaFieldNames() as $metaField) {
            $metaEntity = $records->getAccountMeta($this->guid, $metaField);
            if ($metaEntity === false) {
                $metaEntity = new Storage\Entity\AccountMeta();
            }
            $metaEntity->setGuid($this->guid);
            $metaEntity->setMeta($metaField);
            $metaEntity->setValue($this->form->get($metaField)->getData());
            $records->saveAccountMeta($metaEntity);
            $event->addMetaField($metaField, $metaEntity);
        }

        // Dispatch the account profile post-save event
        $eventDispatcher->dispatch(MembersEvents::MEMBER_PROFILE_POST_SAVE, $event);

        return $this;
    }

    /**
     * Return an existing OAuth record, or create a new one.
     *
     * @param Storage\Records $records
     *
     * @return Storage\Entity\Oauth
     */
    protected function getOauth(Storage\Records $records)
    {
        $oauth = $records->getOauthByResourceOwnerId($this->guid);
        if ($oauth === false) {
            $oauth = $this->createLocalOauthAccount($records);
            $this->createLocalProvider($records);
        }

        return $oauth;
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(Storage\Records $records)
    {
        if ($this->guid === null) {
            throw new \RuntimeException('GUID not set.');
        }

        $this->account = $records->getAccountByGuid($this->guid);
        $this->accountMeta = $records->getAccountMetaAll($this->guid);

        if ($this->account === false) {
            $this->account = new Storage\Entity\Account();
        }

        // Add account fields and meta fields to the form data
        $fields = [
            'displayname' => $this->account->getDisplayname(),
            'email'       => $this->account->getEmail(),
        ];
        if ($this->accountMeta !== false) {
            /** @var Storage\Entity\AccountMeta $metaEntity */
            foreach ((array) $this->accountMeta as $metaEntity) {
                $fields[$metaEntity->getMeta()] = $metaEntity->getValue();
            }
        }

        return [
            'csrf_protection' => true,
            'data'            => $fields,
        ];
    }
}
