<?php

namespace Bolt\Extension\Bolt\Members\Admin;

use Pagerfanta\Pagerfanta;

/**
 * Pager for membership data.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Pager extends Pagerfanta
{
    /**
     * @param array|\Traversable $currentPageResults
     */
    public function setCurrentPageResults($currentPageResults)
    {
        $reflection = new \ReflectionClass($this);
        $prop = $reflection->getParentClass()->getProperty('currentPageResults');
        $prop->setAccessible(true);
        $prop->setValue($this, $currentPageResults);
    }
}
