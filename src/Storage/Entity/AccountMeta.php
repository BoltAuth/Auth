<?php

namespace Bolt\Extension\Bolt\Members\Storage\Entity;

/**
 * Account meta entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccountMeta extends AbstractGuidEntity
{
    /** @var integer */
    protected $id;
    /** @var string */
    protected $meta;
    /** @var string */
    protected $value;

    /**
     * @return string
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param string $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
