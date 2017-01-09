<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Bolt\Extension\Bolt\Members\Exception;
use Bolt\Extension\Bolt\Members\Storage;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Session state class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Session
{
    const COOKIE_AUTHORISATION = 'members';
    const SESSION_ATTRIBUTES = 'members-session-attributes';
    const SESSION_AUTHORISATION = 'members-authorisation';
    const SESSION_STATE = 'members-oauth-state';
    const SESSION_TRANSITIONAL = 'members-transitional';
    const REDIRECT_STACK = 'members-redirect-stack';

    /** @var Authorisation */
    protected $authorisation;
    /** @var Redirect[] */
    protected $redirectStack;
    /** @var AccessToken[] */
    protected $accessTokens;
    /** @var Storage\Entity\Provider */
    protected $transitionalProvider;
    /** @var array */
    protected $attribute;

    /** @var Storage\Records */
    private $records;
    /** @var SessionInterface */
    private $session;
    /** @var string */
    private $homepageUrl;

    /**
     * Constructor.
     *
     * @param Storage\Records  $records
     * @param SessionInterface $session
     * @param string           $homepageUrl
     */
    public function __construct(Storage\Records $records, SessionInterface $session, $homepageUrl)
    {
        $this->records = $records;
        $this->session = $session;
        $this->homepageUrl = $homepageUrl;
    }

    /**
     * Is the session in a transition stage.
     *
     * @return boolean
     */
    public function isTransitional()
    {
        return $this->session->has(self::SESSION_TRANSITIONAL);
    }

    /**
     * Return the transitional provider entity.
     *
     * @return Transition
     */
    public function getTransitionalProvider()
    {
        if ($this->transitionalProvider === null) {
            $this->transitionalProvider = $this->session->get(self::SESSION_TRANSITIONAL);
        }

        return $this->transitionalProvider;
    }

    /**
     * Set the transitional provider entity.
     *
     * @param Transition $transitionalProvider
     *
     * @return Session
     */
    public function setTransitionalProvider(Transition $transitionalProvider)
    {
        $this->session->set(self::SESSION_TRANSITIONAL, $transitionalProvider);
        $this->transitionalProvider = $transitionalProvider;

        return $this;
    }

    /**
     * Remove the transitional provider entity.
     */
    public function removeTransitionalProvider()
    {
        $this->session->remove(self::SESSION_TRANSITIONAL);
    }

    /**
     * Add a provider's access token to the session.
     *
     * @param string      $provider
     * @param AccessToken $accessToken
     *
     * @return Session
     */
    public function addAccessToken($provider, AccessToken $accessToken)
    {
        $this->accessTokens[$provider] = $accessToken;

        return $this;
    }

    /**
     * Return a provider's access token
     *
     * @param string $provider
     *
     * @return AccessToken
     */
    public function getAccessToken($provider)
    {
        if (!isset($this->accessTokens[$provider])) {
            return null;
        }

        return $this->accessTokens[$provider];
    }

    /**
     * Return all access tokens in-use in the session.
     *
     * @return AccessToken[]
     */
    public function getAccessTokens()
    {
        return $this->accessTokens;
    }

    /**
     * Create an authorisation object and persist to the current session.
     *
     * @param string $guid
     *
     * @return Authorisation
     */
    public function createAuthorisation($guid)
    {
        if ($this->accessTokens === null) {
            throw new \RuntimeException(sprintf('Tokens not added to session for member GUID of %s', $guid));
        }
        if ($this->isTransitional()) {
            throw new \RuntimeException(sprintf('Transition still in progress for member GUID of %s', $guid));
        }

        $accountEntity = $this->records->getAccountByGuid($guid);
        $authorisation = new Authorisation();
        $authorisation
            ->setGuid($guid)
            ->setCookie(Uuid::uuid4()->toString())
            ->setAccount($accountEntity)
        ;
        foreach ($this->accessTokens as $provider => $accessToken) {
            $accessToken = $this->setAccessTokenExpires($accessToken);
            $authorisation
                ->addAccessToken($provider, $accessToken)
                ->setExpiry($accessToken->getExpires())
            ;
        }
        $this->setAuthorisation($authorisation);

        return $authorisation;
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
        if ($this->authorisation !== null && !$this->authorisation->getAccount()->isEnabled()) {
            return false;
        }

        return (boolean) $this->authorisation ?: false;
    }

    /**
     * Check if the current logged-in session has a member role.
     *
     * @param string|array $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        $auth = $this->getAuthorisation();
        if ($auth === null) {
            return false;
        }
        $account = $this->records->getAccountByGuid($auth->getGuid());
        if ($account === false) {
            return false;
        }
        $roles = (array) $account->getRoles();

        if (is_string($role)) {
            return in_array($role, $roles);
        }

        return array_intersect($role, $roles) !== false;
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
        if ($tokenEntities === false) {
            return;
        }
        foreach ($tokenEntities as $tokenEntity) {
            $this->records->deleteToken($tokenEntity);
        }
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
        foreach ($this->authorisation->getAccessTokens() as $provider => $accessToken) {
            $tokenEntities = $this->records->getTokensByGuid($this->authorisation->getGuid());
            if ($tokenEntities === false) {
                $tokenEntities[] = new Storage\Entity\Token();
            }

            /** @var Storage\Entity\Token $tokenEntity */
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
        if (empty($this->redirectStack)) {
            return new Redirect($this->homepageUrl);
        }

        $redirect = end($this->redirectStack);
        $key = key($this->redirectStack);
        unset($this->redirectStack[$key]);
        if (empty($this->redirectStack)) {
            $redirect = new Redirect($this->homepageUrl);
            $this->redirectStack[] = $redirect;
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
        $this->redirectStack = [new Redirect($this->homepageUrl)];

        return $this;
    }

    /**
     * Save redirects to the session.
     */
    public function saveRedirects()
    {
        if ($this->session->isStarted()) {
            $this->session->set(self::REDIRECT_STACK, $this->redirectStack);
        }
    }

    /**
     * Load the redirects stored in the session.
     */
    public function loadRedirects()
    {
        if ($this->session->isStarted()) {
            $this->redirectStack = $this->session->get(self::REDIRECT_STACK, [new Redirect($this->homepageUrl)]);
        }
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function hasAttribute($attribute)
    {
        $attributes = $this->session->get(self::SESSION_ATTRIBUTES);
        if (!is_array($attributes)) {
            return false;
        }

        return isset($attributes[$attribute]);
    }

    /**
     * @param string $attribute
     *
     * @return array
     */
    public function getAttribute($attribute)
    {
        $attributes = (array) $this->session->get(self::SESSION_ATTRIBUTES);
        if (!isset($attributes[$attribute])) {
            throw new \RuntimeException(sprintf('Requested attribute "%s" does not exist'), $attribute);
        }

        $value = $attributes[$attribute];
        $attributes = empty($attributes) ? null : $attributes;
        $this->session->set(self::SESSION_ATTRIBUTES, $attributes);

        return $value;
    }

    /**
     * @param string $attribute
     */
    public function removeAttribute($attribute)
    {
        $attributes = (array) $this->session->get(self::SESSION_ATTRIBUTES);
        unset($attributes[$attribute]);

        $this->session->set(self::SESSION_ATTRIBUTES, $attributes);
    }

    /**
     * @param string $attribute
     * @param string $value
     */
    public function setAttribute($attribute, $value)
    {
        $attributes = (array) $this->session->get(self::SESSION_ATTRIBUTES);
        $attributes[$attribute] = $value;

        $this->session->set(self::SESSION_ATTRIBUTES, $attributes);
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
