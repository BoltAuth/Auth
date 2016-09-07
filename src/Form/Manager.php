<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\AccessControl;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Feedback;
use Bolt\Extension\Bolt\Members\Form\Builder;
use Bolt\Extension\Bolt\Members\Form\Entity\Profile;
use Bolt\Extension\Bolt\Members\Form\Type\ProfileEditType;
use Bolt\Extension\Bolt\Members\Storage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Twig_Environment as TwigEnvironment;
use Twig_Markup as TwigMarkup;

/**
 * Form Manager.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Manager
{
    /** @var Config */
    protected $config;
    /** @var AccessControl\Session */
    protected $session;
    /** @var Feedback */
    protected $feedback;
    /** @var Storage\Records  */
    protected $records;
    /** @var Generator */
    private $formGenerator;

    /**
     * Constructor.
     *
     * @param Config                $config
     * @param AccessControl\Session $session
     * @param Feedback              $feedback
     * @param Storage\Records       $records
     * @param Generator             $formGenerator
     */
    public function __construct(
        Config $config,
        AccessControl\Session $session,
        Feedback $feedback,
        Storage\Records $records,
        Generator $formGenerator
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->feedback = $feedback;
        $this->records = $records;
        $this->formGenerator = $formGenerator;
    }

    /**
     * Return the resolved association form.
     *
     * @param Request $request       The client Request object being processed.
     * @param bool    $includeParent Should the template be rendered in a parent, or empty container.
     *
     * @return ResolvedFormBuild
     */
    public function getFormAssociate(Request $request, $includeParent = true)
    {
        $resolvedBuild = new ResolvedFormBuild();
        /** @var Builder\Logout $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_ASSOCIATE, $this->session->getAuthorisation()->getGuid());

        $formAssociate = $builder
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm([])
            ->handleRequest($request)
        ;
        $resolvedBuild->addBuild(MembersForms::FORM_ASSOCIATE, $builder, $formAssociate);

        $extraContext = [
            'twigparent' => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '@Members/authentication/_sub/login.twig',
        ];
        $resolvedBuild->setContext($extraContext);


        return $resolvedBuild;
    }

    /**
     * Return the resolved login form.
     *
     * @param Request $request       The client Request object being processed.
     * @param bool    $includeParent Should the template be rendered in a parent, or empty container.
     *
     * @return ResolvedFormBuild
     */
    public function getFormLogin(Request $request, $includeParent = true)
    {
        $twigParent = $includeParent ? $this->config->getTemplates('authentication', 'parent') : '@Members/authentication/_sub/login.twig';

        return $this->getFormCombinedLogin($request, $twigParent);
    }

    /**
     * Return the resolved logout form.
     *
     * @param Request $request       The client Request object being processed.
     * @param bool    $includeParent Should the template be rendered in a parent, or empty container.
     *
     * @return ResolvedFormBuild
     */
    public function getFormLogout(Request $request, $includeParent = true)
    {
        $resolvedBuild = new ResolvedFormBuild();
        /** @var Builder\Logout $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_LOGOUT);
        $formLogout = $builder
            ->createForm([])
            ->handleRequest($request)
        ;
        $resolvedBuild->addBuild(MembersForms::FORM_LOGOUT, $builder, $formLogout);

        $extraContext = [
            'twigparent' => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '@Members/authentication/_sub/logout.twig',
        ];

        $resolvedBuild->setContext($extraContext);

        return $resolvedBuild;
    }

    /**
     * Return the resolved profile editing form.
     *
     * @param Request $request       The client Request object being processed.
     * @param bool    $includeParent Should the template be rendered in a parent, or empty container.
     * @param string  $guid          Member GUID.
     *
     * @return ResolvedFormBuild
     */
    public function getFormProfileEdit(Request $request, $includeParent = true, $guid = null)
    {
        $resolvedBuild = new ResolvedFormBuild();
        $profile = $this->getEntityProfileEdit($guid);

        /** @var Builder\Profile $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_PROFILE_EDIT, null, $profile);

        /** @var ProfileEditType $type */
        $type = $builder->getType();
        $type->setRequirePassword(false);

        $formEdit = $builder
            ->setAction(sprintf('/%s/profile/edit', $this->config->getUrlMembers()))
            ->createForm([])
            ->handleRequest($request)
        ;
        $resolvedBuild->addBuild(MembersForms::FORM_PROFILE_EDIT, $builder, $formEdit);

        /** @var Builder\Associate $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_ASSOCIATE);
        $formAssociate = $builder
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
            ->createForm([])
            ->handleRequest($request)
        ;
        $resolvedBuild->addBuild(MembersForms::FORM_ASSOCIATE, $builder, $formAssociate);

        $extraContext = [
            'twigparent' => $includeParent ? $this->config->getTemplates('profile', 'parent') : '@Members/profile/_sub/profile.twig',
        ];
        $resolvedBuild->setContext($extraContext);

        return $resolvedBuild;
    }

    /**
     * @param string $guid Member GUID.
     *
     * @return Profile
     */
    private function getEntityProfileEdit($guid = null)
    {
        if ($guid !== null && !Uuid::isValid($guid)) {
            throw new \RuntimeException(sprintf('Invalid GUID value "%s" given.', $guid));
        }

        $account = $this->records->getAccountByGuid($guid);
        $profile = $account ? new Profile($account->toArray()) : new Profile([]);

        $accountMeta = $this->records->getAccountMetaAll($guid);
        if ($accountMeta === false) {
            return $profile;
        }

        /** @var Storage\Entity\AccountMeta $metaEntity */
        foreach ((array) $accountMeta as $metaEntity) {
            if ($profile->has($metaEntity->getMeta())) {
                // Meta shouldn't override
                continue;
            }
            $profile[$metaEntity->getMeta()] = $metaEntity->getValue();
        }


        return $profile;
    }

    /**
     * Return the resolved profile account recovery form.
     *
     * @param Request $request       The client Request object being processed.
     * @param bool    $includeParent Should the template be rendered in a parent, or empty container.
     *
     * @return ResolvedFormBuild
     */
    public function getFormProfileRecovery(Request $request, $includeParent = true)
    {
        /** @var Builder\ProfileRecovery $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_PROFILE_RECOVER_REQUEST, null);
        $form = $builder
            ->createForm([])
            ->handleRequest($request)
        ;
        $extraContext = [
            'twigparent' => $includeParent ? $this->config->getTemplates('authentication', 'parent') : '@Members/profile/_sub/profile.twig',
        ];

        return new ResolvedFormBuild([$form], $extraContext);
    }

    /**
     * Return the resolved registration form.
     *
     * @param Request $request       The client Request object being processed.
     * @param bool    $includeParent Should the template be rendered in a parent, or empty container.
     *
     * @return ResolvedFormBuild
     */
    public function getFormProfileRegister(Request $request, $includeParent = true)
    {
        $twigParent = $includeParent ? $this->config->getTemplates('profile', 'parent') : '@Members/profile/_sub/profile.twig';

// need unique constraint on email
        return $this->getFormCombinedLogin($request, $twigParent);
    }

    /**
     * Render given forms in a template.
     *
     * @param ResolvedFormBuild $resolvedForm
     * @param TwigEnvironment   $twigEnvironment
     * @param string            $template
     * @param array             $context
     *
     * @return TwigMarkup
     */
    public function renderForms(ResolvedFormBuild $resolvedForm, TwigEnvironment $twigEnvironment, $template, array $context = [])
    {
        $context += $resolvedForm->getContext();
        /** @var FormInterface $form */
        foreach ($resolvedForm->getForms() as $form) {
            $formName = sprintf('form_%s', $form->getName());
            $context[$formName] = $form->createView();
        }
        $context['feedback'] = $this->feedback;
        $context['providers'] = $this->config->getEnabledProviders();

        $html = $twigEnvironment->render($template, $context);

        return new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Return the combined login & registration resolved form object.
     *
     * @param Request $request    The client Request object being processed.
     * @param string  $twigParent Parent Twig template to be used.
     *
     * @return ResolvedFormBuild
     */
    protected function getFormCombinedLogin(Request $request, $twigParent)
    {
        $resolvedBuild = new ResolvedFormBuild();
        $resolvedBuild->setContext(['twigparent' => $twigParent]);

        /** @var Builder\Associate $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_ASSOCIATE);
        $builder
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
        ;
        $associateForm = $builder
            ->createForm([])
            ->handleRequest($request)
        ;
        $resolvedBuild->addBuild(MembersForms::FORM_ASSOCIATE, $builder, $associateForm);

        /** @var Builder\LoginOauth $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_LOGIN_OAUTH);
        $builder
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
        ;
        $formOauth = $builder
            ->createForm([])
            ->handleRequest($request)
        ;
        $resolvedBuild->addBuild(MembersForms::FORM_LOGIN_OAUTH, $builder, $formOauth);

        /** @var Builder\LoginPassword $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_LOGIN_PASSWORD);
        $builder
            ->setAction(sprintf('/%s/login', $this->config->getUrlAuthenticate()))
        ;
        $formPassword = $builder
            ->createForm([])
            ->handleRequest($request)
        ;
        $resolvedBuild->addBuild(MembersForms::FORM_LOGIN_PASSWORD, $builder, $formPassword);

        /** @var Builder\ProfileRegister $builder */
        $builder = $this->formGenerator->getFormBuilder(MembersForms::FORM_PROFILE_REGISTER);
        $builder
            ->setAction(sprintf('/%s/profile/register', $this->config->getUrlMembers()))
        ;
        $formRegister = $builder
            ->createForm([])
            ->handleRequest($request)
        ;

        if ($this->session->isTransitional()) {
        }

        $resolvedBuild->addBuild(MembersForms::FORM_PROFILE_REGISTER, $builder, $formRegister);


        return $resolvedBuild;
    }
}
