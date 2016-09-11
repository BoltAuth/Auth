<?php

namespace Bolt\Extension\Bolt\Members\Security\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User entity
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class User implements UserInterface
{
    /** @var integer */
    private $id;
    /** @var string */
    private $username;
    /** @var string */
    private $apiKey;

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}
