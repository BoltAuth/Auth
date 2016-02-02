<?php

namespace Bolt\Extension\Bolt\Members\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Exception event.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
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
