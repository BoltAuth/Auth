<?php

namespace Bolt\Extension\Bolt\Members\Form\Builder;

use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Form\Entity;
use Bolt\Extension\Bolt\Members\Form\Type;
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
class Profile extends AbstractFormBuilder
{
    /** @var Type\ProfileEditType */
    protected $type;
    /** @var Entity\Profile */
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
     * @return Profile
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

        foreach ($fields as $fieldName => $fieldValue) {
            //we use the bolt MagicAttributeTrait for convenience
            $this->entity->$fieldName = $fieldValue;
        }
        $data = parent::getData($records);

        return $data + [
            'data'            => $this->entity,
        ];
    }
}
