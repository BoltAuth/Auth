<?php

namespace Bolt\Extension\BoltAuth\Auth\Pager;

/**
 * Pager class for admin paging.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class PagerEntity
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
     * @return PagerEntity
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
     * @return PagerEntity
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
     * @return PagerEntity
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
     * @return PagerEntity
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
     * @return PagerEntity
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
     * @return PagerEntity
     */
    public function setShowingTo($showingTo)
    {
        $this->showingTo = $showingTo;

        return $this;
    }
}
