<?php

namespace Bolt\Extension\Bolt\Members\Security;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Security\Entity\User;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Cookie authenticator class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class CookieAuthenticator extends AbstractGuardAuthenticator
{
    /** @var Session */
    protected $session;
    /** @var Records */
    protected $records;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * Constructor.
     *
     * @param Session               $session
     * @param Records               $records
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Session $session, Records $records, UrlGeneratorInterface $urlGenerator)
    {
        $this->session = $session;
        $this->records = $records;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        if ($request->cookies->has(Session::COOKIE_AUTHORISATION)) {
            return $request->cookies->get(Session::COOKIE_AUTHORISATION);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var InMemoryUserProvider $userProvider */

        return new User();
        //$apiKey = $credentials['token'];
dump($userProvider);
die(__METHOD__);
        // if null, authentication will fail
        // if a User object, checkCredentials() is called
        return $this->em->getRepository('AppBundle:User')->findOneBy(['apiKey' => $apiKey]);
//$this->records->getAccountByGuid();
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case
die(__METHOD__);
        // return true to cause authentication success
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response(strtr($exception->getMessageKey(), $exception->getMessageData()), 403);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->urlGenerator->generate('authenticationLogin'));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return true;
    }
}
