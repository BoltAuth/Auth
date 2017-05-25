<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Exception event.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MembersExceptionEvent extends Event
{
    const ERROR = 'members.Error';

    /** @var \Exception */
    private $exception;

    /**
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Return the Exception.
     */
    public function getException()
    {
        return $this->exception;
    }
}
