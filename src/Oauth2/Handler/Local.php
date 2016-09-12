<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Bolt\Extension\Bolt\Members\Storage\Entity;
use PasswordLib\Password\Implementation\Blowfish;
use Ramsey\Uuid\Uuid;
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
    /** @var Form */
    private $submittedForm;

    /**
     * {@inheritdoc}
     */
    public function login(Request $request)
    {
        if (parent::login($request)) {
            return $this->session->popRedirect()->getResponse();
        }

        if ($this->submittedForm === null) {
            throw new \RuntimeException(sprintf('%s::%s requires a %s object to be set.', __CLASS__, __METHOD__, Form::class));
        }

        $account = $this->records->getAccountByEmail($this->submittedForm->get('email')->getData());
        if (!$account instanceof Entity\Account) {
            $this->setDebugMessage('Login email address does not match a stored member record.');

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

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, $grantType = 'password')
    {
        $account = $this->records->getAccountByEmail($this->submittedForm->get('email')->getData());
        $oauth = $this->records->getOauthByGuid($account->getGuid());
        $requestPassword = $this->submittedForm->get('password')->getData();

        if ($this->isValidPassword($oauth, $requestPassword) === false) {
            return null;
        }

        $accessToken = $this->provider->getAccessToken('password', []);
        $this->session
            ->addAccessToken('local', $accessToken)
            ->createAuthorisation($account->getGuid())
        ;
        $this->feedback->info('Login successful.');
        $request->query->set('code', Uuid::uuid4()->toString());

        parent::process($request, $grantType);

        return $this->session->popRedirect()->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request)
    {
        parent::logout($request);
    }

    /**
     * @param Form $submittedForm
     */
    public function setSubmittedForm(Form $submittedForm)
    {
        $this->submittedForm = $submittedForm;
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
