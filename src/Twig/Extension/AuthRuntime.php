<?php

namespace Bolt\Extension\BoltAuth\Auth\Twig\Extension;

use Bolt\Extension\BoltAuth\Auth\AccessControl;
use Bolt\Extension\BoltAuth\Auth\AccessControl\Session;
use Bolt\Extension\BoltAuth\Auth\Config\Config;
use Bolt\Extension\BoltAuth\Auth\Form;
use Bolt\Extension\BoltAuth\Auth\Storage;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment as TwigEnvironment;
use Twig_Extension as TwigExtension;
use Twig_Markup as TwigMarkup;

/**
 * Twig runtime functions.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AuthRuntime extends TwigExtension
{
    /** @var Config */
    private $config;
    /** @var Form\Manager */
    private $formManager;
    /** @var Storage\Records */
    private $records;
    /** @var AccessControl\Session */
    private $session;
    /** @var UrlGeneratorInterface */
    private $generator;

    /**
     * Constructor.
     *
     * @param Config                $config
     * @param Form\Manager          $formManager
     * @param Storage\Records       $records
     * @param AccessControl\Session $session
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(
        Config $config,
        Form\Manager $formManager,
        Storage\Records $records,
        AccessControl\Session $session,
        UrlGeneratorInterface $generator
    ) {
        $this->config = $config;
        $this->formManager = $formManager;
        $this->records = $records;
        $this->session = $session;
        $this->generator = $generator;
    }

    /**
     * Check if the current session is a logged-in auth.
     *
     * @return bool
     */
    public function isAuth()
    {
        return $this->session->hasAuthorisation();
    }

    /**
     * Return a auth's account.
     *
     * @param string|null $guid
     *
     * @return Storage\Entity\Auth|null
     */
    public function getAuth($guid = null)
    {
        if ($guid === null) {
            if (!$this->session->hasAuthorisation()) {
                return null;
            }
            $auth = $this->session->getAuthorisation();
            $guid = $auth->getGuid();
        }
        $account = $this->records->getAccountByGuid($guid);
        if ($account) {
            $meta = $this->records->getAccountMetaAll($guid);
            $auth = new Storage\Entity\Auth($account, $meta);

            return $auth;
        }

        return null;
    }

    /**
     * Return a auth's account meta data.
     *
     * @param string|null $guid
     *
     * @return Storage\Entity\AccountMeta[]|null
     */
    public function getAuthMeta($guid = null)
    {
        if ($guid === null) {
            if (!$this->session->hasAuthorisation()) {
                return null;
            }
            $auth = $this->session->getAuthorisation();
            $guid = $auth->getGuid();
        }
        $meta = $this->records->getAccountMetaAll($guid);

        return $meta ?: null;
    }

    /**
     * Fetch OAuth data from session if set.
     *
     * Data in session:
     * [
     *     'providerName'  => string
     *     'accessToken'   => \League\OAuth2\Client\Token
     *     'resourceOwner' => \League\OAuth2\Client\Provider\ResourceOwnerInterface
     * ]
     *
     * @internal
     *
     * @return array|null
     */
    public function getAuthOauth()
    {
        if (!$this->session->hasAttribute(AccessControl\Session::SESSION_ATTRIBUTE_OAUTH_DATA)) {
            return null;
        }

        $data = $this->session->getAttribute(AccessControl\Session::SESSION_ATTRIBUTE_OAUTH_DATA);
        if (isset($data['resourceOwner']) && $data['resourceOwner'] instanceof ResourceOwnerInterface) {
            return $data['resourceOwner'];
        }

        return null;
    }

    /**
     * Check if the current logged-in session has a auth role.
     *
     * @param string|array $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->session->hasRole($role);
    }

    /**
     * Return an array of registered OAuth providers for an account.
     *
     * @param string $guid
     *
     * @return array
     */
    public function getProviders($guid = null)
    {
        $providers = [];
        if ($guid === null) {
            $auth = $this->session->getAuthorisation();

            if ($auth === null) {
                return $providers;
            }
            $guid = $auth->getGuid();
        }

        $providerEntities = $this->records->getProvisionsByGuid($guid);
        if ($providerEntities === false) {
            return $providers;
        }

        /** @var Storage\Entity\Provider $providerEntity */
        foreach ($providerEntities as $providerEntity) {
            $providers[] = $providerEntity->getProvider();
        }

        return $providers;
    }

    /**
     * Return last seen date from all OAuth providers for an account
     *
     * @param string $guid
     *
     * @return \DateTime
     */
    public function getProvidersLastSeen($guid = null)
    {
        if ($guid === null) {
            $auth = $this->session->getAuthorisation();

            if ($auth === null) {
                return null;
            }
            $guid = $auth->getGuid();
        }

        $providerEntities = $this->records->getProvisionsByGuid($guid);
        if ($providerEntities === false) {
            return null;
        }

        /** @var Storage\Entity\Provider $providerEntity */
        $lastseen = null;
        $lastupdate = null;
        foreach ($providerEntities as $providerEntity) {
            $provider['lastseen']   = $providerEntity->getLastSeen();
            $provider['lastupdate'] = $providerEntity->getLastUpdate();

            if ($lastseen <= $provider['lastseen']) {
                $lastseen = $provider['lastseen'];
            }
            if ($lastupdate <= $provider['lastupdate']) {
                $lastupdate = $provider['lastupdate'];
            }
        }
        // make an empty last seen date the same as the last update date
        if (empty($lastseen) && !empty($lastupdate)) {
            $lastseen = $lastupdate;
        }

        return $lastseen;
    }

    /**
     * Display login/logout button(s) depending on status.
     *
     * @param TwigEnvironment $twigEnvironment
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderSwitcher(TwigEnvironment $twigEnvironment, $template = null)
    {
        if ($this->session->getAuthorisation()) {
            return $this->renderLogout($twigEnvironment, $template);
        }

        return $this->renderLogin($twigEnvironment, $template);
    }

    /**
     * Display social login buttons to associate with an existing account.
     *
     * @param TwigEnvironment $twigEnvironment
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderAssociate(TwigEnvironment $twigEnvironment, $template = null)
    {
        if (!$this->session->hasAuthorisation()) {
            return $this->renderLogin($twigEnvironment, $template);
        }

        $template = $template ?: $this->config->getTemplate('authentication', 'associate');
        $form = $this->formManager->getFormAssociate(new Request(), false);
        $html = $this->formManager->renderForms($form, $twigEnvironment, $template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display logout button(s).
     *
     * @param TwigEnvironment $twigEnvironment
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderLogin(TwigEnvironment $twigEnvironment, $template = null)
    {
        $context = ['transitional' => $this->session->isTransitional()];
        $template = $template ?: $this->config->getTemplate('authentication', 'login');
        $form = $this->formManager->getFormLogin(new Request(), false);
        $html = $this->formManager->renderForms($form, $twigEnvironment, $template, $context);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display logout button.
     *
     * @param TwigEnvironment $twigEnvironment
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderLogout(TwigEnvironment $twigEnvironment, $template = null)
    {
        $template = $template ?: $this->config->getTemplate('authentication', 'logout');
        $form = $this->formManager->getFormLogout(new Request(), false);
        $html = $this->formManager->renderForms($form, $twigEnvironment, $template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display that profile editing form.
     *
     * @param TwigEnvironment $twigEnvironment
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderEdit(TwigEnvironment $twigEnvironment, $template = null)
    {
        if (!$this->session->hasAuthorisation()) {
            return $this->renderLogin($twigEnvironment, $template);
        }

        $guid = $this->session->getAuthorisation()->getGuid();
        $template = $template ?: $this->config->getTemplate('profile', 'edit');
        $form = $this->formManager->getFormProfileEdit(new Request(), false, $guid);
        $html = $this->formManager->renderForms($form, $twigEnvironment, $template);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Display the registration form.
     *
     * @param TwigEnvironment $twigEnvironment
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderRegister(TwigEnvironment $twigEnvironment, $template = null)
    {
        $context = ['transitional' => $this->session->isTransitional()];
        $template = $template ?: $this->config->getTemplate('profile', 'register');
        $form = $this->formManager->getFormProfileRegister(new Request(), false);
        $html = $this->formManager->renderForms($form, $twigEnvironment, $template, $context);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Get the URL for login.
     *
     * @param int $format
     *
     * @return string
     */
    public function getLinkLogin($format = UrlGeneratorInterface::RELATIVE_PATH)
    {
        return $this->generator->generate('authenticationLogin', [], $format);
    }

    /**
     * Get the URL for logout.
     *
     * @param int $format
     *
     * @return string
     */
    public function getLinkLogout($format = UrlGeneratorInterface::RELATIVE_PATH)
    {
        return $this->generator->generate('authenticationLogout', [], $format);
    }

    /**
     * Get the URL for password reset.
     *
     * @param int $format
     *
     * @return string
     */
    public function getLinkReset($format = UrlGeneratorInterface::RELATIVE_PATH)
    {
        return $this->generator->generate('authenticationPasswordReset', [], $format);
    }

    /**
     * Get the URL for profile viewing.
     *
     * @param int $format
     *
     * @return string
     */
    public function getLinkView($format = UrlGeneratorInterface::RELATIVE_PATH)
    {
        return $this->generator->generate('authProfileView', [], $format);
    }

    /**
     * Get the URL for profile editing.
     *
     * @param int $format
     *
     * @return string
     */
    public function getLinkEdit($format = UrlGeneratorInterface::RELATIVE_PATH)
    {
        return $this->generator->generate('authProfileEdit', [], $format);
    }

    /**
     * Get the URL for profile registration.
     *
     * @param int $format
     *
     * @return string
     */
    public function getLinkRegister($format = UrlGeneratorInterface::RELATIVE_PATH)
    {
        return $this->generator->generate('authProfileRegister', [], $format);
    }


    /**
     * Get the all current feedback for Auth to use in twig.
     *
     * @param int $format
     *
     * @return string
     */
    public function getFeedback($peek = true)
    {
        if($peek) {
            return $this->session->getFeedback()->peek();
        } else {
            return $this->session->getFeedback()->get();
        }
    }

    /**
     * Display all current feedback for Auth.
     *
     * @param TwigEnvironment $twigEnvironment
     * @param string          $template
     *
     * @return TwigMarkup
     */
    public function renderFeedback(TwigEnvironment $twigEnvironment, $template = null)
    {
        $context = ['feedback' => $this->session->getFeedback()];
        $template = $template ?: $this->config->getTemplate('feedback', 'feedback');
        // use the profile view form because it should be read only and harmless
        $form = $this->formManager->getFormProfileView(new Request());
        $html = $this->formManager->renderForms($form, $twigEnvironment, $template, $context);

        return new TwigMarkup($html, 'UTF-8');
    }

}
