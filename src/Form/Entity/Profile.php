<?php

namespace Bolt\Extension\Bolt\Members\Form\Entity;

use Bolt\Extension\Bolt\Members\Storage\Entity\AccountMeta;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User profile object class
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Profile
{
    /** @var string */
    protected $guid;
    /** @var string */
    protected $email;
    /** @var string */
    protected $displayName;
    /** @var array */
    protected $meta;
    /** @var  Records */
    private $records;

    /**
     * Constructor.
     *
     * @param Records $records
     */
    public function __construct(Records $records)
    {
        $this->records = $records;
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
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
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Profile
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     *
     * @return Profile
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }


    /**
     * @param bool $cache
     *
     * @return AccountMeta[]
     */
    public function getMeta($cache = true)
    {
        if ($cache && $this->meta !== null) {
            return $this->meta;
        }

        return $this->meta = $this->records->getAccountMetaAll($this->guid);
    }
}
