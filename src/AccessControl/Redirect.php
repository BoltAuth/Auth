<?php

namespace Bolt\Extension\BoltAuth\Auth\AccessControl;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Redirection stacking class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Redirect
{
    /** @var string */
    protected $target;

    /**
     * Constructor.
     *
     * @param string $target
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * @return RedirectResponse
     */
    public function getResponse()
    {
        return new RedirectResponse($this->target);
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     *
     * @return Redirect
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }
}
