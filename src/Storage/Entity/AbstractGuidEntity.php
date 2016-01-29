<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Bolt core GUID primary entity.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractGuidEntity extends Entity
{
    protected $guid;

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @param string $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->guid;
    }

    /**
     * @param string $guid
     */
    public function setId($guid)
    {
        $this->guid = $guid;
    }
}
