<?php

namespace Bolt\Extension\Bolt\Members\Storage;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\AccessControl\Validator\AccountVerification;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Form\Entity\Profile;
use Bolt\Extension\Bolt\Members\Storage;
use Carbon\Carbon;
use League\OAuth2\Client\Provider\AbstractProvider;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;

/**
 * Profile persistence manager.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class FormEntityHandler
{
    /** @var Storage\Records */
    private $records;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var Config */
    private $config;
    /** @var AccessControl\Session */
    private $session;

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param Storage\Records          $records
     * @param EventDispatcherInterface $eventDispatcher
     * @param AccessControl\Session    $session
     */
    public function __construct(
        Config $config,
        Storage\Records $records,
        EventDispatcherInterface $eventDispatcher,
        AccessControl\Session $session
    ) {
        $this->config = $config;
        $this->records = $records;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
    }

    /**
     * Save a user view/edit form data.
     *
     * @param Storage\Entity\Account $account
     * @param Form                   $form
     *
     * @return FormEntityHandler
     */
    public function saveProfileForm(Storage\Entity\Account $account, Form $form)
    {
        $guid = $account->getId();
        if ($guid === null) {
            throw new \RuntimeException('GUID not set.');
        }

        $account->setDisplayname($form->get('displayname')->getData());
        $account->setEmail($form->get('email')->getData());

        // Dispatch the account profile pre-save event
        $event = new MembersProfileEvent($account);
        $this->eventDispatcher->dispatch(MembersEvents::MEMBER_PROFILE_PRE_SAVE, $event);

        $this->records->saveAccount($account);

        $password = $form->get('password')->getData();
        if ($password !== null) {
            $oauth = $this->getOauth($guid);
            if ($oauth === false) {
                $oauth = $this->createLocalOauthAccount($guid, $password);
                $this->createLocalProviderEntity($guid);
            }
            $oauth->setPassword($password);

            $this->records->saveOauth($oauth);
        }

        // Save any defined meta fields
        foreach ($event->getMetaEntityNames() as $metaField) {
            $metaEntity = $this->records->getAccountMeta($guid, $metaField);
            if ($metaEntity === false) {
                $metaEntity = new Storage\Entity\AccountMeta();
            }
            $metaEntity->setGuid($guid);
            $metaEntity->setMeta($metaField);
            $metaEntity->setValue($form->get($metaField)->getData());
            $this->records->saveAccountMeta($metaEntity);
            $event->addMetaEntity($metaEntity);
        }

        // Dispatch the account profile post-save event
        $this->eventDispatcher->dispatch(MembersEvents::MEMBER_PROFILE_POST_SAVE, $event);

        return $this;
    }

    /**
     * @param string $guid
     * @param Form   $form
     *
     * @return FormEntityHandler
     */
    public function saveProfileRecoveryForm($guid, Form $form)
    {
        /** @var Storage\Entity\Oauth $oauth */
        $oauth = $this->records->getOauthByGuid($guid);
        if ($oauth !== false) {
            $password = $form->get('password')->getData();
            $oauth->setPassword($password);
            $this->records->saveOauth($oauth);
        }

        return $this;
    }

    /**
     * Save a profile registration form.
     *
     * @param Profile          $entity
     * @param Form             $form
     * @param AbstractProvider $provider
     * @param string           $providerName
     *
     * @return FormEntityHandler
     */
    public function saveProfileRegisterForm(Profile $entity, Form $form, AbstractProvider $provider, $providerName)
    {
        // Create and store the account record
        $account = $this->createAccount($entity);
        $guid = $account->getGuid();

        // Create the event
        $event = new MembersProfileEvent($account);
        // Create verification meta
        $this->createAccountVerificationKey($event, $guid);
        // Create a local OAuth account record
        $password = $form->get('password')->getData();
        if ($password) {
            $this->createLocalOauthAccount($guid, $password);
            $this->createLocalProviderEntity($guid);
        }

        // Create a provider entry
        if ($this->session->isTransitional()) {
            $accessToken = $this->session->getTransitionalProvider()->getAccessToken();
            $this->convertTransitionalProviderToEntity($guid);
        } else {
            $accessToken = $provider->getAccessToken('password', ['guid' => $account->getGuid()]);
        }

        // Set up the initial session.
        $this->session
            ->addAccessToken($providerName, $accessToken)
            ->createAuthorisation($guid)
        ;

        // Dispatch the account profile post-save event
        $this->eventDispatcher->dispatch(MembersEvents::MEMBER_PROFILE_REGISTER, $event);

        return $this;
    }

    /**
     * Return an existing OAuth record, or create a new one.
     *
     * @param string $guid
     *
     * @return Storage\Entity\Oauth
     */
    protected function getOauth($guid)
    {
        return $this->records->getOauthByResourceOwnerId($guid);
    }

    /**
     * Create a local OAuth account record.
     *
     * @param string $guid
     * @param string $password
     *
     * @return Storage\Entity\Oauth
     */
    protected function createLocalOauthAccount($guid, $password)
    {
        $oauth = $this->records->createOauth($guid, $guid, true);
        $oauth->setPassword($password);

        $this->records->saveOauth($oauth);

        return $oauth;
    }

    /**
     * Create a 'local' provider record.
     *
     * @param string $guid
     *
     * @return Storage\Entity\Provider
     */
    protected function createLocalProviderEntity($guid)
    {
        $provider = $this->records->createProviderEntity($guid, 'local', $guid);

        return $provider;
    }

    /**
     * Create and store the account record.
     *
     * @param Profile $entity
     *
     * @return Storage\Entity\Account
     */
    protected function createAccount(Profile $entity)
    {
        $displayName = $entity->getDisplayname();
        $emailAddress = $entity->getEmail();
        $account = $this->records->createAccount($displayName, $emailAddress, $this->config->getRolesRegister());

        $this->records->saveAccount($account);

        return $account;
    }

    /**
     * Create account verification key profile meta.
     *
     * @param MembersProfileEvent $event
     * @param string              $guid
     */
    protected function createAccountVerificationKey(MembersProfileEvent $event, $guid)
    {
        $metaValue = sha1(Uuid::uuid4()->toString());

        // Set the email verification key in the account meta
        $meta = new Storage\Entity\AccountMeta();
        $meta->setGuid($guid);
        $meta->setMeta(AccountVerification::KEY_NAME);
        $meta->setValue($metaValue);

        $this->records->saveAccountMeta($meta);

        $event->addMetaEntryNames([AccountVerification::KEY_NAME => $metaValue]);
    }

    /**
     * Create a 'remote' provider record from a session stored 'transitional' one.
     *
     * @param string $guid
     *
     * @return Storage\Entity\Provider
     */
    protected function convertTransitionalProviderToEntity($guid)
    {
        $provider = $this->session->getTransitionalProvider()->getProviderEntity();
        $provider->setGuid($guid);
        $provider->setLastupdate(Carbon::now());

        $this->records->saveProvider($provider);

        $this->session->removeTransitionalProvider();

        return $provider;
    }
}
