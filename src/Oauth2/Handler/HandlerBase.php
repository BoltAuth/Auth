<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Bolt\Extension\Bolt\Members\AccessControl\Authorisation;
use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\AccessControl\Transition;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Event\MembersLoginEvent;
use Bolt\Extension\Bolt\Members\Exception as Ex;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Oauth2\Client\Provider\ResourceOwnerInterface;
use Bolt\Extension\Bolt\Members\Oauth2\Client\ProviderManager;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Carbon\Carbon;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Authorisation control class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class HandlerBase
{
    /** @var Config */
    protected $config;
    /** @var AbstractProvider */
    protected $provider;
    /** @var ProviderManager */
    protected $providerManager;
    /** @var string */
    protected $providerName;
    /** @var Entity\Provider */
    protected $providerEntity;
    /** @var Request */
    protected $request;
    /** @var Records */
    protected $records;
    /** @var Session */
    protected $session;
    /** @var Feedback */
    protected $feedback;
    /** @var LoggerInterface */
    protected $logger;
    /** @var EventDispatcherInterface */
    protected $dispatcher;
    /** @var ResourceOwnerInterface */
    protected $resourceOwner;

    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @param Config      $config
     * @param Application $app
     */
    public function __construct(Config $config, Application $app)
    {
        $this->config = $config;
        $this->provider = $app['members.oauth.provider'];
        $this->providerManager = $app['members.oauth.provider.manager'];
        $this->providerName = $app['members.oauth.provider.name'];
        $this->records = $app['members.records'];
        $this->session = $app['members.session'];
        $this->feedback = $app['members.feedback'];
        $this->logger = $app['logger.system'];
        $this->dispatcher = $app['dispatcher'];

        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    protected function login(Request $request)
    {
        $providerName = $this->providerManager->getProviderName();
        $provider = $this->config->getProvider($providerName);

        if (!$provider->isEnabled()) {
            throw new Ex\DisabledProviderException('Invalid provider setting.');
        }

        if ($this->session->hasAuthorisation()) {
            return true;
        }

        // Set user feedback messages
        $this->feedback->info('Login was route complete, redirecting for authentication.');
    }

    /**
     * {@inheritdoc}
     */
    protected function logout(Request $request)
    {
        if ($this->session->hasAuthorisation()) {
            $this->session->removeAuthorisation();
            $this->feedback->info('Logout was successful.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function process(Request $request, $grantType)
    {
        // Check that state token matches the stored one
        $this->session->checkStateToken($request);
        $accessToken = $this->getAccessToken($request, $grantType);
        $this->setSession($accessToken);

        if ($this->session->isTransitional()) {
            $this->handleAccountTransition($accessToken);
        }

        $this->providerEntity->setLastupdate(Carbon::now());
        $this->records->saveProvider($this->providerEntity);

        // Send the event
        $this->dispatchEvent(MembersEvents::MEMBER_LOGIN, $this->session->getAuthorisation());
    }

    /**
     * Handle a successful account authentication.
     *
     * @param AccessToken $accessToken
     *
     * @throws Ex\MissingAccountException
     * @throws Ex\InvalidAuthorisationRequestException
     *
     * @return string
     */
    protected function handleAccountTransition(AccessToken $accessToken)
    {
        $providerName = $this->providerManager->getProviderName();
        $resourceOwner = $this->getResourceOwner($accessToken);
        $email = $resourceOwner->getEmail();

        if ((bool) $email === false) {
            // Redirect to registration
            $this->setDebugMessage(sprintf('No email address found for transitional %s provider ID %s', $providerName, $resourceOwner->getId()));

            throw new Ex\MissingAccountException(sprintf('Provider %s data for ID %s does not include an email address.', $providerName, $resourceOwner->getId()));
        }

        $accountEntity = $this->records->getAccountByEmail($email);
        if ($accountEntity === false) {
            $this->setDebugMessage(sprintf('No account found for transitional %s provider ID %s', $providerName, $resourceOwner->getId()));

            throw new Ex\MissingAccountException(sprintf('No account for %s provider ID %s during transition', $providerName, $resourceOwner->getId()));
        }

        $providerEntity = $this->session->getTransitionalProvider()->getProviderEntity();
        $providerEntity->setGuid($accountEntity->getGuid());
        $providerEntity->setLastupdate(Carbon::now());
        $this->records->saveProvider($providerEntity);
        $this->session->removeTransitionalProvider();

        $this->setSession($accessToken);
    }

    /**
     * Set up the session for this request.
     *
     * @param AccessToken $accessToken
     */
    protected function setSession(AccessToken $accessToken)
    {
        $providerName = $this->providerManager->getProviderName();
        $resourceOwner = $this->getResourceOwner($accessToken);
        $this->providerEntity = $this->records->getProvisionByResourceOwnerId($providerName, $resourceOwner->getId());

        // Member is already in possession of another login, and the provider exists, add the access token
        if ($this->session->hasAuthorisation() && $this->providerEntity !== false) {
            $this->session
                ->getAuthorisation()
                ->addAccessToken($providerName, $accessToken)
            ;
            $this->setDebugMessage(sprintf('Adding %s access token %s for ID %s', $providerName, $accessToken, $resourceOwner->getId()));

            return;
        }

        // Existing user with a new login, and the provider exists
        if ($this->providerEntity !== false) {
            $this->session
                ->addAccessToken($providerName, $accessToken)
                ->createAuthorisation($this->providerEntity->getGuid())
            ;
            $this->setDebugMessage(sprintf(
                'Creating authorisation  for GUID %s, and %s provider access token %s for ID %s',
                $this->providerEntity->getGuid(),
                $providerName,
                $accessToken,
                $resourceOwner->getId()
            ));

            return;
        }

        // New provider call
        $this->createProviderTransition($accessToken, $resourceOwner);
    }

    /**
     * Create a new provider entity object.
     *
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     */
    protected function createProviderTransition(AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        // Create a new provider entry
        $providerName = $this->providerManager->getProviderName();
        $transition = new Transition($providerName, $accessToken, $resourceOwner);

        $this->session
            ->addAccessToken($providerName, $accessToken)
            ->setTransitionalProvider($transition)
        ;

        $this->setDebugMessage(sprintf('Creating provider profile for %s ID %s', $providerName, $resourceOwner->getId()));
        $this->setDebugMessage(sprintf(
            'Creating provisional %s provider entity for access token %s for ID %s',
            $providerName,
            $accessToken,
            $resourceOwner->getId()
        ));
    }

    /**
     * Check that a GUID we've been given is valid.
     *
     * @param string $guid
     *
     * @throws \RuntimeException
     */
    protected function assertValidGuid($guid)
    {
        if (strlen($guid) !== 36) {
            throw new \RuntimeException('Invalid GUID value being used!');
        }
    }

    /**
     * Query the provider for the resrouce owner.
     *
     * @param AccessToken $accessToken
     *
     * @throws IdentityProviderException
     *
     * @return ResourceOwnerInterface
     */
    protected function getResourceOwner(AccessToken $accessToken)
    {
        if ($this->resourceOwner === null) {
            $this->resourceOwner = $this->provider->getResourceOwner($accessToken);
        }

        return $this->resourceOwner;
    }

    /**
     * Get an access token from the OAuth provider.
     *
     * @param Request $request
     * @param string  $grantType One of the following:
     *                           - 'authorization_code'
     *                           - 'password'
     *                           - 'refresh_token'
     *
     * @throws IdentityProviderException
     * @throws Ex\InvalidAuthorisationRequestException
     *
     * @return AccessToken
     */
    protected function getAccessToken(Request $request, $grantType)
    {
        $code = $request->query->get('code');

        if ($code === null) {
            $this->setDebugMessage('Attempt to get an OAuth2 acess token with an empty code in the request.');

            throw new Ex\InvalidAuthorisationRequestException('No provider access code.');
        }
        $options = ['code' => $code];

        // Try to get an access token using the authorization code grant.
        $accessToken = $this->provider->getAccessToken($grantType, $options);
        $this->setDebugMessage('OAuth token received: ' . json_encode($accessToken));
        try {
            $accessToken->hasExpired();

            return $accessToken;
        } catch (\RuntimeException $e) {
            return new AccessToken([
                'access_token'      => $accessToken->getToken(),
                'resource_owner_id' => $this->getResourceOwner($accessToken)->getResourceOwnerId(),
                'refresh_token'     => $accessToken->getRefreshToken(),
                'expires_in'        => 3600,
            ]);
        }
    }

    /**
     * Write a debug message to both the debug log and the feedback array.
     *
     * @param string $message
     */
    protected function setDebugMessage($message)
    {
        $this->logger->debug('[Members][Handler]: ' . $message, ['event' => 'extensions']);
        $this->feedback->debug($message);
    }

    /**
     * Dispatch event to any listeners.
     *
     * @param string        $type          Either MembersEvents::MEMBER_LOGIN' or MembersEvents::MEMBER_LOGOUT
     * @param Authorisation $authorisation
     */
    protected function dispatchEvent($type, Authorisation $authorisation)
    {
        if (!$this->dispatcher->hasListeners($type)) {
            return;
        }

        $event = new MembersLoginEvent($authorisation);
        try {
            $this->dispatcher->dispatch($type, $event);
        } catch (\Exception $e) {
            if ($this->config->debugEnabled()) {
                dump($e);
            }

            $this->logger->critical('Members event dispatcher had an error', ['event' => 'exception', 'exception' => $e]);
        }
    }
}
