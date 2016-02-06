<?php

namespace Bolt\Extension\Bolt\Members;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Feedback message class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Feedback implements EventSubscriberInterface
{
    const SESSION_KEY = 'members-feedback';

    /** @var SessionInterface */
    protected $session;
    /** @var array */
    protected $feedback = [];

    /**
     * Constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

        if ($this->session->isStarted() && $stored = $this->session->get(self::SESSION_KEY)) {
            $this->feedback = $stored;
            $this->session->remove(self::SESSION_KEY);
        }
    }

    /**
     * Peek at the saved feedback array.
     *
     * @return array
     */
    public function peek()
    {
        return $this->feedback;
    }

    /**
     * Get the saved feedback array and flush.
     *
     * @return array
     */
    public function get()
    {
        $feedback = $this->feedback;
        $this->feedback = [];

        return $feedback;
    }

    /**
     * Set a feedback error of message that will be passed to Twig as a global.
     *
     * @param string $state
     * @param string $message
     *
     * @throws \InvalidArgumentException
     */
    public function set($state, $message)
    {
        if (empty($state) || !in_array($state, ['debug', 'error', 'info'])) {
            throw new \InvalidArgumentException("Feedback state can only be 'error', 'message', or 'debug'.");
        }
        $this->feedback[$state][] = $message;
    }

    /**
     * Set an debug message.
     *
     * @param string $message
     */
    public function debug($message)
    {
        $this->set('debug', $message);
    }

    /**
     * Set an error message.
     *
     * @param string $message
     */
    public function error($message)
    {
        $this->set('error', $message);
    }

    /**
     * Set an info message.
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->set('info', $message);
    }

    /**
     * Post-request middleware callback, added in service provider.
     */
    public function onResponse()
    {
        if ($this->session->isStarted()) {
            $this->session->set(self::SESSION_KEY, $this->feedback);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse'],
        ];
    }
}
