<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Bolt\Extension\Bolt\Members\AccessControl\Authorisation;
use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\MembersEvent;
use Bolt\Extension\Bolt\Members\Event\MembersEvents;
use Bolt\Extension\Bolt\Members\Exception as Ex;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Oauth2\Client\ProviderManager;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Carbon\Carbon;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorisation control class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
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
            return;
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
        $guid = $this->handleAccountTransition($accessToken);

        // Update the PHP session
        $authorisation = $this->session->createAuthorisation($guid, $this->providerManager->getProviderName(), $accessToken);
        // Send the event
        $this->dispatchEvent(MembersEvents::MEMBER_LOGIN, $authorisation);
    }

    /**
     * Handle a successful account authentication.
     *
     * @param AccessToken $accessToken
     *
     * @throws Ex\InvalidAuthorisationRequestException
     * @throws Ex\MissingAccountException
     *
     * @return string
     */
    protected function handleAccountTransition(AccessToken $accessToken)
    {
        $providerName = $this->providerManager->getProviderName(true);
        $authorisation = $this->session->getAuthorisation();

        $providerEntity = false;
        $providerEntities = $this->records->getProvisionsByGuid($authorisation->getGuid());
        if ($authorisation === null) {
            throw new Ex\InvalidAuthorisationRequestException('Session authorisation missing');
        }
        $authorisation->addAccessToken($providerName, $accessToken);
        $resourceOwner = $this->getResourceOwner($accessToken);

        /** @var Entity\Provider $entity */
        foreach ((array) $providerEntities as $entity) {
            if ($entity->getProvider() === $providerName) {
                $providerEntity = $entity;
                $this->setDebugMessage(sprintf('Profile provider found for %s ID %s with GUID of %s', $providerName, $resourceOwner->getId(), $providerEntity->getGuid()));
                break;
            }
        }

        if ($providerEntity === false) {
            // Create a new provider entry
            $this->setDebugMessage(sprintf('No provider profile found for %s ID %s', $providerName, $resourceOwner->getId()));
            $providerEntity = new Entity\Provider();
            $providerEntity->setGuid($authorisation->getGuid());
            $providerEntity->setProvider($providerName);
            $providerEntity->setRefreshToken($accessToken->getRefreshToken());
            $providerEntity->setResourceOwnerId($resourceOwner->getId());
            $providerEntity->setResourceOwner($resourceOwner);
        }

        // Update the provider record
        $providerEntity->setLastupdate(Carbon::now());
        $this->records->saveProvider($providerEntity);

        // Update the session token record
        $this->setDebugMessage(sprintf('Writing session token for %s ID %s', $providerName, $resourceOwner->getId()));

        $authorisation->addAccessToken($providerName, $accessToken);
        $providerEntity->setLastupdate(Carbon::now());
        $this->records->saveProvider($providerEntity);

        return $providerEntity->getGuid();
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
        return $this->provider->getResourceOwner($accessToken);
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
                'resource_owner_id' => $accessToken->getResourceOwnerId(),
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
     * @param string       $type         Either MembersEvents::MEMBER_LOGIN' or MembersEvents::MEMBER_LOGOUT
     * @param Authorisation $authorisation
     */
    protected function dispatchEvent($type, Authorisation $authorisation)
    {
        if (!$this->dispatcher->hasListeners($type)) {
            return;
        }

        $event = new MembersEvent($authorisation);
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
