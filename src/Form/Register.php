<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
use Bolt\Extension\Bolt\Members\Storage;
use Carbon\Carbon;
use League\OAuth2\Client\Provider\AbstractProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Register form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Register extends AbstractForm
{
    /** @var Type\RegisterType */
    protected $type;
    /** @var Entity\Register */
    protected $entity;
    /** @var string */
    protected $clientIp;
    /** @var AbstractProvider */
    protected $provider;
    /** @var Session */
    protected $session;
    /** @var array */
    protected $roles;

    /**
     * @param string $clientIp
     *
     * @return Register
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * @param AbstractProvider $provider
     *
     * @return Register
     */
    public function setProvider(AbstractProvider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @param array $roles
     *
     * @return Register
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param Session $session
     *
     * @return Register
     */
    public function setSession(Session $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function saveForm(Storage\Records $records, EventDispatcherInterface $eventDispatcher)
    {
        if ($this->clientIp === null) {
            throw new \RuntimeException('Client IP not set.');
        }
        if ($this->provider === null) {
            throw new \RuntimeException('OAuth provider not set.');
        }
        if ($this->roles === null) {
            throw new \RuntimeException('Roles not set.');
        }
        if ($this->session === null) {
            throw new \RuntimeException('Members session not set.');
        }

        // Create and store the account entity
        $account = new Storage\Entity\Account();
        $account->setDisplayname($this->form->get('displayname')->getData());
        $account->setEmail($this->form->get('email')->getData());
        $account->setRoles($this->roles);
        $account->setEnabled(true);
        $account->setLastseen(Carbon::now());
        $account->setLastip($this->clientIp);
        $records->saveAccount($account);

        // Save the password to a meta record
        $encryptedPassword = password_hash($this->form->get('plainPassword')->getData(), PASSWORD_BCRYPT);
        $oauth = new Storage\Entity\Oauth();
        $oauth->setGuid($account->getGuid());
        $oauth->setResourceOwnerId($account->getGuid());
        $oauth->setEnabled(true);
        $oauth->setPassword($encryptedPassword);
        $records->saveOauth($oauth);

        // Set up the initial session.
        $localAccessToken = $this->provider->getAccessToken('password', []);
        $this->session
            ->addAccessToken('local', $localAccessToken)
            ->createAuthorisation($account->getGuid())
        ;

        // Create a local provider entry
        $provider = new Storage\Entity\Provider();
        $provider->setGuid($account->getGuid());
        $provider->setProvider('local');
        $provider->setResourceOwnerId($account->getGuid());
        $provider->setLastupdate(Carbon::now());
        $records->saveProvider($provider);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(Storage\Records $records)
    {
        if ($this->session === null) {
            throw new \RuntimeException('Members session not set.');
        }

        $fields = [];
        if ($this->session->isTransitional()) {
            $resourceOwner = $this->session->getTransitionalProvider()->getResourceOwner();
            $fields = [
                'displayname' => $resourceOwner->getName(),
                'email'       => $resourceOwner->getEmail(),
            ];
        }

        return [
            'csrf_protection' => true,
            'data'            => $fields,
        ];
    }
}
