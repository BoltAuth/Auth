<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Bolt\Extension\Bolt\Members\Exception;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Bolt\Extension\Bolt\Members\Storage\Records;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Session state class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Session implements EventSubscriberInterface
{
    const COOKIE_AUTHORISATION = 'members';
    const SESSION_AUTHORISATION = 'members-authorisation';
    const SESSION_STATE = 'members-oauth-state';
    const REDIRECT_STACK = 'members-redirect-stack';

    /** @var Authorisation */
    protected $authorisation;
    /** @var Redirect[] */
    protected $redirectStack;

    /** @var Records */
    private $records;
    /** @var SessionInterface */
    private $session;

    /**
     * Constructor.
     *
     * @param Records          $records
     * @param SessionInterface $session
     */
    public function __construct(Records $records, SessionInterface $session)
    {
        $this->records = $records;
        $this->session = $session;
    }

    /**
     * Create an authorisation object and persist to the current session.
     *
     * @param string      $guid
     * @param string      $provider
     * @param AccessToken $accessToken
     *
     * @throws \RuntimeException
     *
     * @return Authorisation
     */
    public function createAuthorisation($guid, $provider, AccessToken $accessToken)
    {
        if (!$this->records->getAccountByGuid($guid) instanceof Entity\Account) {
            throw new \RuntimeException(sprintf('Attempted to create authorisation session for invalid account GUID: %s', $guid));
        }

        $accessToken = $this->setAccessTokenExpires($accessToken);
        $authorisation = new Authorisation();
        $authorisation->setGuid($guid)
            ->setCookie(Uuid::uuid4()->toString())
            ->addAccessToken($provider, $accessToken)
            ->setExpiry($accessToken->getExpires())
        ;
        $this->setAuthorisation($authorisation);

        return $authorisation;
    }

    /**
     * Add a provider's access token to the authorisation session.
     *
     * @param string      $provider
     * @param AccessToken $accessToken
     */
    public function addProviderAccessToken($provider, AccessToken $accessToken)
    {
        if ($this->authorisation === null) {
            throw new \RuntimeException(sprintf('Authorisation session has not been set up yet. Unable to add %s provider', $provider));
        }

        $this->authorisation->addAccessToken($provider, $accessToken);
        $this->setAuthorisation($this->authorisation);
    }

    /**
     * Check if there is a valid authorisation session.
     *
     * @return boolean
     */
    public function hasAuthorisation()
    {
        if ($this->authorisation === null) {
            $this->getAuthorisation();
        }

        return (boolean) $this->authorisation ?: false;
    }

    /**
     * Return the stored authorisation session.
     *
     * @return Authorisation|null
     */
    public function getAuthorisation()
    {
        if ($this->authorisation === null && $this->session->get(self::SESSION_AUTHORISATION)) {
            $this->authorisation = Authorisation::createFromJson($this->session->get(self::SESSION_AUTHORISATION));
        }

        return $this->authorisation;
    }

    /**
     * Save an authorisation session.
     *
     * @param Authorisation $authorisation
     */
    public function setAuthorisation(Authorisation $authorisation)
    {
        $this->authorisation = $authorisation;
        $this->session->set(self::SESSION_AUTHORISATION, json_encode($authorisation));
    }

    /**
     * Remove authorisation.
     */
    public function removeAuthorisation()
    {
        $authorisation = $this->getAuthorisation();

        // Remove property
        $this->authorisation = null;
        // Clear session
        $this->session->remove(self::SESSION_AUTHORISATION);

        if ($authorisation === null) {
            return;
        }

        // Remove records
        $tokenEntities = $this->records->getTokensByGuid($authorisation->getGuid());
        foreach ((array) $tokenEntities as $tokenEntity) {
            $this->records->deleteToken($tokenEntity);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['persistData'],
                ['saveRedirects']
            ],
            KernelEvents::REQUEST => [
                ['loadRedirects']
            ],
        ];
    }

    /**
     * Persist session data to storage.
     */
    public function persistData()
    {
        if ($this->authorisation === null) {
            return;
        }

        /** @var AccessToken $accessToken */
        foreach ($this->authorisation->getAccessTokens() as $provider => $accessToken){
            $tokenEntities = $this->records->getTokensByGuid($this->authorisation->getGuid());
            if ($tokenEntities === false) {
                $tokenEntities[] = new Entity\Token();
            }

            /** @var Entity\Token $tokenEntity */
            foreach ($tokenEntities as $tokenEntity) {
                $tokenEntity->setGuid($this->authorisation->getGuid());
                $tokenEntity->setToken((string) $accessToken);
                $tokenEntity->setTokenType('access_token');
                $tokenEntity->setTokenData($accessToken);
                $tokenEntity->setExpires($accessToken->getExpires());
                $tokenEntity->setCookie($this->authorisation->getCookie());

                $this->records->saveToken($tokenEntity);
            }
        }

        $this->session->set(self::SESSION_AUTHORISATION, json_encode($this->authorisation));
    }

    /**
     * Return the stored provider session state token.
     *
     * @return string
     */
    public function getStateToken()
    {
        return $this->session->get(self::SESSION_STATE);
    }

    /**
     * Set the state token string from a provider to the user's session.
     *
     * @param AbstractProvider $provider
     */
    public function setStateToken(AbstractProvider $provider)
    {
        $this->session->set(self::SESSION_STATE, $provider->getState());
    }

    /**
     * Remove the state token from the user's session.
     */
    public function removeStateToken()
    {
        $this->session->remove(self::SESSION_STATE);
    }

    /**
     * Check the state token stored in session against the one passed in the request.
     *
     * @param Request $request
     *
     * @throws Exception\InvalidAuthorisationRequestException
     *
     * @return bool
     */
    public function checkStateToken(Request $request)
    {
        $requestState = $request->get('state');
        if ($requestState === null) {
            //$this->logMessage(LogLevel::ERROR, 'Authorisation request was missing state token.');
            throw new Exception\InvalidAuthorisationRequestException('Invalid authorisation request!');
        }

        // Get the stored token
        $storedState = $this->getStateToken();

        // Clear the stored token from the session
        $this->removeStateToken();

        if (empty($storedState) || $storedState !== $requestState) {
            //$this->logMessage(LogLevel::ERROR, "Mismatch of state token '$state' against saved '$stateToken'");
            throw new Exception\InvalidAuthorisationRequestException('Invalid authorisation request!');
        }

        return true;
    }

    /**
     * Add a redirect onto the stack.
     *
     * @param string $url
     */
    public function addRedirect($url)
    {
        $this->redirectStack[] = new Redirect($url);
    }

    /**
     * Remove and return a redirect off the stack.
     *
     * @return Redirect
     */
    public function popRedirect()
    {
        $redirect = end($this->redirectStack);
        $key = key($this->redirectStack);
        unset($this->redirectStack[$key]);
        if (empty($this->redirectStack)) {
            $this->redirectStack[] = new Redirect('/');
        }

        return $redirect;
    }

    /**
     * Clear the redirect stack.
     *
     * @return Session
     */
    public function clearRedirects()
    {
        $this->redirectStack = null;
        $this->redirectStack[] = new Redirect('/');

        return $this;
    }

    /**
     * Save redirects to the session.
     */
    public function saveRedirects()
    {
        $this->session->set(self::REDIRECT_STACK, $this->redirectStack);
    }

    /**
     * Load the redirects stored in the session.
     */
    public function loadRedirects()
    {
        $this->redirectStack = $this->session->get(self::REDIRECT_STACK, [new Redirect('/')]);
    }

    /**
     * Ensure an access token always has a valid expiry field.
     *
     * @param AccessToken $accessToken
     *
     * @return AccessToken
     */
    private function setAccessTokenExpires(AccessToken $accessToken)
    {
        if ($accessToken->getExpires() !== null) {
            return $accessToken;
        }

        // Set the expiry to one hour in the future
        $tokenData = json_decode(json_encode($accessToken), true);
        $tokenData['expiry'] = 3600;

        return new AccessToken($tokenData);
    }
}
