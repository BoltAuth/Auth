<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Account meta entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccountMeta extends AbstractGuidEntity
{
    protected $id;
    protected $meta;
    protected $value;

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
