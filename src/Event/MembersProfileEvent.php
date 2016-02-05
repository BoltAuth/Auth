<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Bolt\Extension\Bolt\Members\AccessControl\Role;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Members profile event class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class MembersProfileEvent extends GenericEvent
{
    /** @var array */
    protected $metaFields;

    /**
     * @return array
     */
    public function getMetaFields()
    {
        return (array) $this->metaFields;
    }

    /**
     * @param array $metaFields
     *
     * @return MembersProfileEvent
     */
    public function setMetaFields(array $metaFields)
    {
        if ($this->metaFields === null) {
            $this->metaFields = $metaFields;
        } else {
            $this->metaFields = array_merge($this->metaFields, $metaFields);
        }

        return $this;
    }
}
