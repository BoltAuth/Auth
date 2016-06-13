<?php

namespace Bolt\Extension\Bolt\Members\Admin;

/**
 * Pager class for admin paging.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Pager
{
    /** @var string */
    protected $for;
    /** @var int */
    protected $current;
    /** @var int */
    protected $count;
    /** @var int */
    protected $totalPages;
    /** @var int */
    protected $showingFrom;
    /** @var int */
    protected $showingTo;

    public function makeLink()
    {
        return '?page=';
    }

    /**
     * @return string
     */
    public function getFor()
    {
        return $this->for;
    }

    /**
     * @param string $for
     *
     * @return Pager
     */
    public function setFor($for)
    {
        $this->for = $for;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @param int $current
     *
     * @return Pager
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return Pager
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @param int $totalPages
     *
     * @return Pager
     */
    public function setTotalPages($totalPages)
    {
        $this->totalPages = $totalPages;

        return $this;
    }

    /**
     * @return int
     */
    public function getShowingFrom()
    {
        return $this->showingFrom;
    }

    /**
     * @return int
     */
    public function getShowing_from()
    {
        return $this->showingFrom;
    }

    /**
     * @param int $showingFrom
     *
     * @return Pager
     */
    public function setShowingFrom($showingFrom)
    {
        $this->showingFrom = $showingFrom;

        return $this;
    }

    /**
     * @return int
     */
    public function getShowingTo()
    {
        return $this->showingTo;
    }

    /**
     * @return int
     */
    public function getShowing_to()
    {
        return $this->showingTo;
    }

    /**
     * @param int $showingTo
     *
     * @return Pager
     */
    public function setShowingTo($showingTo)
    {
        $this->showingTo = $showingTo;

        return $this;
    }
}
