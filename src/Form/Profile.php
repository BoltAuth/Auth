<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Profile form.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Profile extends AbstractForm
{
    /** @var Type\ProfileType */
    protected $type;
    /** @var Entity\Profile */
    protected $entity;
    /** @var Storage\Entity\Account */
    protected $account;
    /** @var Storage\Entity\AccountMeta */
    protected $accountMeta;
    /** @var string */
    protected $guid;

    /**
     * @param string $guid
     *
     * @return Profile
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
        $records->saveAccount($this->account);

        if ($this->form->get('plainPassword')->getData() !== null) {
            $encryptedPassword = password_hash($this->form->get('plainPassword')->getData(), PASSWORD_BCRYPT);
            $oauth = $records->getOauthByResourceOwnerId($this->account->getGuid());
            $oauth->setPassword($encryptedPassword);
            $records->saveOauth($oauth);
        }

        // Dispatch the account profile save event
        $event = new MembersProfileEvent();
        $eventDispatcher->dispatch(MembersEvents::MEMBER_PROFILE_SAVE, $event);

        // Save any defined meta fields
        foreach ($event->getMetaFields() as $metaField) {
            $metaEntity = $records->getAccountMeta($this->guid, $metaField);
            if ($metaEntity === false) {
                $metaEntity = new Storage\Entity\AccountMeta();
            }
            $metaEntity->setGuid($this->guid);
            $metaEntity->setMeta($metaField);
            $metaEntity->setValue($this->form->get($metaField)->getData());
            $records->saveAccountMeta($metaEntity);
        }

        return $this;
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
