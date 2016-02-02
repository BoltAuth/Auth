<?php

namespace Bolt\Extension\Bolt\Members;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Feedback message class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Feedback
{
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

        if ($this->session->isStarted() && $stored = $this->session->get('members_feedback')) {
            $this->feedback = $stored;
            $this->session->remove('members_feedback');
        }
    }

    /**
     * Post-request middleware callback, added in service provider.
     */
    public function after()
    {
        if ($this->session->isStarted()) {
            $this->session->set('members_feedback', $this->feedback);
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
        if (empty($state) || !in_array($state, ['error', 'message', 'debug'])) {
            throw new \InvalidArgumentException("Feedback state can only be 'error', 'message', or 'debug'.");
        }
        $this->feedback[$state][] = $message;
    }
}
