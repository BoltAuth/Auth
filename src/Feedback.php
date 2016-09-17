<?php

namespace Bolt\Extension\Bolt\Members;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * Feedback message class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Feedback implements SessionBagInterface
{
    const SESSION_KEY = 'members-feedback.cache';

    /** @var array */
    protected $feedback;

    /** @var string */
    private $name = 'members.feedback';
    /** @var string */
    private $storageKey;
    /** @var bool */
    private $isDebug;

    /**
     * Constructor.
     *
     * @param string $storageKey The key used to store flashes in the session
     * @param bool   $isDebug
     */
    public function __construct($storageKey, $isDebug)
    {
        $this->storageKey = $storageKey;
        $this->isDebug = $isDebug;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array &$feedback)
    {
        $this->feedback = &$feedback;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageKey()
    {
        return $this->storageKey;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->get();
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
        $feedback = (array) $this->feedback;
        $this->feedback = null;

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

        // Don't log debug messages when not debugging
        if ($state === 'debug' && $this->isDebug === false) {
            return;
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
}
