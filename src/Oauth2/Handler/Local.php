<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use PasswordLib\Password\Factory as PasswordFactory;
use PasswordLib\Password\Implementation\Blowfish;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * OAuth local login provider.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Local extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    public function login(Request $request, Form $submittedForm = null)
    {
        if (parent::login($request)) {
            return $this->session->popRedirect()->getResponse();
        }

        $account = $this->records->getAccountByEmail($submittedForm->get('email')->getData());
        if (!$account instanceof Entity\Account) {
            $this->setDebugMessage('Login details do not match a stored record.');

            return null;
        }

        $oauth = $this->records->getOauthByGuid($account->getGuid());
        if (!$oauth instanceof Entity\Oauth) {
            $this->feedback->info('Registration is required.');

            return new RedirectResponse($this->urlGenerator->generate('membersProfileRegister'));
        }

        if (!$oauth->getEnabled()) {
            $this->feedback->info('Account disabled.');

            return new RedirectResponse($this->urlGenerator->generate('authenticationLogin'));
        }

        $requestPassword = $submittedForm->get('password')->getData();
        if ($this->isValidPassword($oauth, $requestPassword)) {
            $accessToken = $this->provider->getAccessToken('password', []);
            $this->session
                ->addAccessToken('local', $accessToken)
                ->createAuthorisation($account->getGuid())
            ;
            $this->feedback->info('Login successful.');

            return $this->session->popRedirect()->getResponse();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, $grantType = 'authorization_code')
    {
        parent::process($request, $grantType);
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request)
    {
        parent::logout($request);
    }

    /**
     * Check to see if a provided password is valid.
     *
     * @param Entity\Oauth $oauth
     * @param string       $requestPassword
     *
     * @return bool
     */
    protected function isValidPassword(Entity\Oauth $oauth, $requestPassword)
    {
        if (!Blowfish::detect($oauth->getPassword())) {
            return false;
        }

        // We have a Blowfish hash, verify
        return password_verify($requestPassword, $oauth->getPassword());
    }
}
