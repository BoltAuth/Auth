<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Members profile event class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
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
