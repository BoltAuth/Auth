<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

/**
 * Bolt core ID assumption mapping trait.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
trait IdGuidMappingTrait
{
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
