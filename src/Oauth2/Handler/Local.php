<?php

namespace Bolt\Extension\Bolt\Members\Oauth2\Handler;

use Bolt\Extension\Bolt\Members\Form\LoginPassword;
use Bolt\Extension\Bolt\Members\Storage\Entity;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * OAuth local login provider.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Local extends HandlerBase
{
    /**
     * {@inheritdoc}
     */
    public function login(Request $request)
    {
        if (parent::login($request)) {
            return;
        }
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
     * Handle password login attempt.
     *
     * @param Form                  $submittedForm
     * @param LoginPassword         $formService
     * @param UrlGeneratorInterface $urlGeneratorInterface
     *
     * @return RedirectResponse|null
     */
    public function getLoginResponse(Form $submittedForm, LoginPassword $formService, UrlGeneratorInterface $urlGeneratorInterface)
    {
        $account = $this->records->getAccountByEmail($submittedForm->get('email')->getData());
        if (!$account instanceof Entity\Account) {
            return null;
        }

        $oauth = $this->records->getOauthByGuid($account->getGuid());
        if (!$oauth instanceof Entity\Oauth) {
            $this->feedback->info('Registration is required.');

            return new RedirectResponse($urlGeneratorInterface->generate('membersProfileRegister'));
        }

        if (!$oauth->getEnabled()) {
            $this->feedback->info('Account disabled.');

            return new RedirectResponse($urlGeneratorInterface->generate('authenticationLogin'));
        }

        if (password_verify($submittedForm->get('password')->getData(), $oauth->getPassword())) {
            $formService->saveForm($this->records, $this->dispatcher);

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
}
