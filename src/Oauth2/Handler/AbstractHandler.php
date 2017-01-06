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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Authorisation control class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractHandler
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
    /** @var ResourceOwnerInterface */
    protected $resourceOwner;
    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

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
        $this->urlGenerator = $app['url_generator'];
    }

    /**
     * Handle login.
     *
     * @param Request $request
     *
     * @throws Ex\DisabledProviderException
     *
     * @return bool
     */
    protected function login(Request $request)
    {
        $providerName = $this->providerManager->getProviderName();
        $provider = $this->config->getProvider($providerName);

        if (!$provider->isEnabled()) {
            throw new Ex\DisabledProviderException('Invalid provider setting.');
        }

        if ($this->session->hasAuthorisation()) {
            $this->setDebugMessage('Session has existing authorisation.');

            return true;
        }

        // Set user feedback messages
        $this->setDebugMessage(sprintf('Login was route complete for %s, redirecting for authentication.', $request->getRequestUri()));

        return false;
    }

    /**
     * Handle logout.
     *
     * @param Request $request
     */
    protected function logout(Request $request)
    {
        if ($this->session->hasAuthorisation()) {
            $this->session->removeAuthorisation();
            $this->feedback->info('Logout was successful.');
            $this->setDebugMessage(sprintf('Logout was route complete for %s', $request->getRequestUri()));
        } else {
            $this->setDebugMessage('Logout was no required. Members session not found.');
        }
    }

    /**
     * Process the login request.
     *
     * @param Request $request
     * @param string  $grantType
     *
     * @throws Ex\DisabledAccountException
     * @throws Ex\InvalidAuthorisationRequestException
     * @throws Ex\MissingAccountException
     */
    protected function process(Request $request, $grantType)
    {
        $code = $request->query->get('code');
        if ($code === null) {
            $this->setDebugMessage('Attempt to get an OAuth2 access token with an empty code in the request.');

            throw new Ex\InvalidAuthorisationRequestException('No provider access code.');
        }

        if ($this->session->hasAuthorisation()) {
            return;
        }

        //either no Auth or more probably a disabled account... anyway we cant continue
        $exceptionMsg = 'No valid authorisation, the account may be disabled';
        $this->setDebugMessage($exceptionMsg);
        $this->feedback->error($exceptionMsg);
        throw new Ex\DisabledAccountException($exceptionMsg);
    }

    /**
     * Finish the authentication.
     *
     * @param Request $request
     */
    protected function finish(Request $request)
    {
        $now = Carbon::now();
        $guid = $this->session->getAuthorisation()->getGuid();
        $provision = $this->getProviderEntityByGuid($guid, $this->providerName);
        if ($provision === false) {
            throw new \RuntimeException('Unable to finish. Missing provider entry.');
        }

        $provision->setLastseen($now);
        $provision->setLastip($request->getClientIp());
        $provision->setLastupdate($now);

        $this->records->saveProvider($provision);

        // Send the event
        $this->dispatchEvent(MembersEvents::MEMBER_LOGIN, $this->session->getAuthorisation());
    }

    /**
     * Handle a successful account authentication.
     *
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @throws Ex\MissingAccountException
     */
    protected function handleAccountTransition(AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        $providerName = $this->providerManager->getProviderName();
        $email = $resourceOwner->getEmail();

        if ((bool) $email === false) {
            // Redirect to registration
            $this->setDebugMessage(sprintf('No email address found for transitional %s provider ID %s', $providerName, $resourceOwner->getId()));

            throw new Ex\MissingAccountException(sprintf('Provider %s data for ID %s does not include an email address.', $providerName, $resourceOwner->getId()));
        }

        $guid = $this->session->getAuthorisation()->getGuid();
        $accountEntity = $this->records->getAccountByGuid($guid);
        if ($accountEntity === false) {
            $accountEntity = $this->records->getAccountByEmail($email);
        }
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

        if ($this->hasProviderEntity($providerName, $resourceOwner->getId())) {
            $this->setSessionExistingProvider($providerName, $accessToken, $resourceOwner);
        } else {
            $this->setSessionNewProvider($providerName, $accessToken, $resourceOwner);
        }
    }

    /**
     * .
     *
     * @param string                 $providerName
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     */
    protected function setSessionNewProvider($providerName, AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        if ($this->session->hasAuthorisation()) {
            // Member is already in possession of another login, and the provider does NOT exist
            $this->createProviderTransition($accessToken, $resourceOwner);

            return;
        }

        $account = $this->records->getAccountByEmail($resourceOwner->getEmail());
        if ($account === false) {
            $account = $this->records->createAccount(
                $resourceOwner->getName(),
                $resourceOwner->getEmail(),
                $this->config->getRolesRegister()
            );
        }

        $providerEntity = new Entity\Provider();
        $providerEntity->setGuid($account->getGuid());
        $providerEntity->setProvider($providerName);
        $providerEntity->setResourceOwner($resourceOwner);
        $providerEntity->setResourceOwnerId($resourceOwner->getId());
        $providerEntity->setLastupdate(Carbon::now());

        $this->records->saveProvider($providerEntity);

        $this->session
            ->addAccessToken($providerName, $accessToken)
            ->createAuthorisation($providerEntity->getGuid())
        ;
    }

    /**
     * Set up a session for an existing provider registration.
     *
     * @param string                 $providerName
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     */
    protected function setSessionExistingProvider($providerName, AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        if ($this->session->hasAuthorisation()) {
            // Member is already in possession of another login, and the provider exists, add the access token
            $this->session
                ->getAuthorisation()
                ->addAccessToken($providerName, $accessToken)
            ;
            $this->setDebugMessage(sprintf('Adding %s access token %s for ID %s', $providerName, $accessToken, $resourceOwner->getId()));

            return;
        }
        $resourceOwnerId = $resourceOwner->getId();
        $providerEntity = $this->getProviderEntityByResourceOwnerId($providerName, $resourceOwnerId);
        if ($providerEntity === false) {
            throw new \RuntimeException('Provider entity does not exist yet.');
        }
        $guid = $providerEntity->getGuid();

        // Existing user with a new login, and the provider exists
        $this->session
            ->addAccessToken($providerName, $accessToken)
            ->createAuthorisation($guid)
        ;
        $this->setDebugMessage(sprintf(
            'Creating authorisation  for GUID %s, and %s provider access token %s for ID %s',
            $guid,
            $providerName,
            $accessToken,
            $resourceOwner->getId()
        ));
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

        // If we have an authorisation, this is for an existing and logged in account,
        // else we either have a new registration or one we need to find an associate.
        $guid = $this->session->getAuthorisation()->getGuid();
        $transition = new Transition($guid, $providerName, $accessToken, $resourceOwner);

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
     * Query the provider for the resource owner.
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
     * @param string $grantType One of the following:
     *                          - 'authorization_code'
     *                          - 'password'
     *                          - 'refresh_token'
     * @param array  $options
     *
     * @return AccessToken
     */
    protected function getAccessToken($grantType, array $options)
    {
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
     * @param string        $type          Either MembersEvents::MEMBER_LOGIN' or MembersEvents::MEMBER_LOGOUT
     * @param Authorisation $authorisation
     */
    protected function dispatchEvent($type, Authorisation $authorisation)
    {
        if (!$this->dispatcher->hasListeners($type)) {
            return;
        }

        $event = new MembersLoginEvent();
        $event->setAccount($authorisation->getAccount());

        try {
            $this->dispatcher->dispatch($type, $event);
        } catch (\Exception $e) {
            if ($this->config->isDebug()) {
                dump($e);
            }

            $this->logger->critical('Members event dispatcher had an error', ['event' => 'exception', 'exception' => $e]);
        }
    }

    /**
     * @param string $providerName
     * @param string $resourceOwnerId
     *
     * @return Entity\Provider|false
     */
    protected function hasProviderEntity($providerName, $resourceOwnerId)
    {
        $providerEntity = $this->records->getProvisionByResourceOwnerId($providerName, $resourceOwnerId);

        return $providerEntity instanceof Entity\Provider;
    }

    /**
     * Return the provider entity.
     *
     * @param string $providerName
     * @param string $resourceOwnerId
     *
     * @throws \RuntimeException
     *
     * @return Entity\Provider
     */
    protected function getProviderEntityByResourceOwnerId($providerName, $resourceOwnerId)
    {
        return $this->records->getProvisionByResourceOwnerId($providerName, $resourceOwnerId);
    }

    /**
     * Return the provider entity.
     *
     * @param string $providerName
     * @param string $guid
     *
     * @throws \RuntimeException
     *
     * @return Entity\Provider
     */
    protected function getProviderEntityByGuid($guid, $providerName)
    {
        return $this->records->getProvision($guid, $providerName);
    }
}
