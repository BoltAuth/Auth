<?php

namespace Bolt\Extension\BoltAuth\Auth\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Combined auth entity.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Auth extends Entity
{
    /** @var string */
    protected $guid;
    /** @var string */
    protected $email;
    /** @var \DateTime */
    protected $lastseen;
    /** @var string */
    protected $lastip;
    /** @var string */
    protected $displayname;
    /** @var boolean */
    protected $enabled;
    /** @var boolean */
    protected $verified;
    /** @var array */
    protected $roles;
    /** @var array */
    protected $metaFields = [];

    /**
     * Constructor.
     *
     * @param Account             $account
     * @param AccountMeta[]|false $accountMetas
     */
    public function __construct(Account $account, $accountMetas)
    {
        parent::__construct($account);
        if ($accountMetas === false) {
            return;
        }

        /** @var AccountMeta $accountMeta */
        foreach ($accountMetas as $accountMeta) {
            $meta = $accountMeta->getMeta();
            $value = $accountMeta->getValue();
            $method = 'set' . ucfirst($meta);
            $this->$method($value);

            $this->metaFields[] = $meta;
        }
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return \DateTime
     */
    public function getLastseen()
    {
        return $this->lastseen;
    }

    /**
     * @return string
     */
    public function getLastip()
    {
        return $this->lastip;
    }

    /**
     * @return string
     */
    public function getDisplayname()
    {
        return $this->displayname;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return boolean
     */
    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function getMetaFields()
    {
        return $this->metaFields;
    }
}
