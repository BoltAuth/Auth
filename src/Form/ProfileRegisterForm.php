<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider;
use Bolt\Extension\Bolt\Members\Storage;
use Carbon\Carbon;
use League\OAuth2\Client\Provider\AbstractProvider;
use Ramsey\Uuid\Uuid;
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
class ProfileRegisterForm extends BaseProfile
{
    /** @var Type\ProfileRegisterType */
    protected $type;
    /** @var Entity\ProfileRegister */
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
     * @return ProfileRegisterForm
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * @param AbstractProvider $provider
     *
     * @return ProfileRegisterForm
     */
    public function setProvider(AbstractProvider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @param array $roles
     *
     * @return ProfileRegisterForm
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param Session $session
     *
     * @return ProfileRegisterForm
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

        // Create and store the account record
        $account = $this->createAccount($records);
        // Create the event
        $event = new MembersProfileEvent($account);
        // Create verification meta
        $this->createAccountVerificationKey($records, $event);

        // Create a local OAuth account record
        if ($this->form->get('plainPassword')->getData()) {
            $this->createLocalOauthAccount($records);
            $provider = $this->createLocalProvider($records);
        }

        // Create a provider entry
        if ($this->session->isTransitional()) {
            $accessToken = $this->session->getTransitionalProvider()->getAccessToken();
            $provider = $this->createRemoteProvider($records);
        } else {
            $accessToken = $this->provider->getAccessToken('password', []);
        }

        // Set up the initial session.
        $this->session
            ->addAccessToken($provider->getProvider(), $accessToken)
            ->createAuthorisation($this->guid)
        ;

        // Dispatch the account profile pre-save event
        $eventDispatcher->dispatch(MembersEvents::MEMBER_PROFILE_REGISTER, $event);

        return $this;
    }

    /**
     * Create and store the account record.
     *
     * @param Storage\Records $records
     *
     * @return Storage\Entity\Account
     */
    protected function createAccount(Storage\Records $records)
    {
        $account = new Storage\Entity\Account();
        $account->setDisplayname($this->form->get('displayname')->getData());
        $account->setEmail($this->form->get('email')->getData());
        $account->setRoles($this->roles);
        $account->setEnabled(true);
        $account->setVerified(false);
        $account->setLastseen(Carbon::now());
        $account->setLastip($this->clientIp);

        $records->saveAccount($account);

        // Keep track of the new GUID
        $this->guid = $account->getGuid();

        return $account;
    }

    protected function createAccountVerificationKey(Storage\Records $records, MembersProfileEvent $event)
    {
        $metaName = 'account-verification-key';
        $metaValue = sha1(Uuid::uuid4()->toString());

        // Set the email verification key in the account meta
        $meta = new Storage\Entity\AccountMeta();
        $meta->setGuid($this->guid);
        $meta->setMeta($metaName);
        $meta->setValue($metaValue);

        $records->saveAccountMeta($meta);

        $event->setMetaFields([$metaName => $metaValue]);
    }

    /**
     * Create a 'remote' provider record.
     *
     * @param Storage\Records $records
     *
     * @return Storage\Entity\Provider
     */
    protected function createRemoteProvider(Storage\Records $records)
    {
        $provider = $this->session->getTransitionalProvider()->getProviderEntity();
        $provider->setGuid($this->guid);
        $provider->setLastupdate(Carbon::now());

        $records->saveProvider($provider);

        $this->session->removeTransitionalProvider();

        return $provider;
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
