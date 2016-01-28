<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

/**
 * Single role class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Role
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $displayName;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $displayName
     */
    public function __construct($name, $displayName)
    {
        $this->name = $name;
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return Role
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }
}
