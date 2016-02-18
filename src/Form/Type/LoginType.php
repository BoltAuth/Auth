<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Login type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class LoginType extends AbstractType
{
    /** @var Config */
    private $config;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',       EmailType::class,   [
                'label'       => Trans::__('Email:'),
                'data'        => $this->getData($options, 'email'),
                'constraints' => new Assert\Email([
                    'message' => 'The address "{{ value }}" is not a valid email.',
                    'checkMX' => true,
                ]),
            ])
            ->add('password', PasswordType::class, [
                'label'       => Trans::__('Password:'),
                'data'        => null,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 6]),
                ],
            ])
            ->add('submit',   'submit', [
                'label'   => Trans::__('Login'),
            ])
        ;
        $this->addProviderButtons($builder);
    }

    public function getName()
    {
        return 'login';
    }

    /**
     * Add any configured provider buttons to the form.
     *
     * @param FormBuilderInterface $builder
     */
    private function addProviderButtons(FormBuilderInterface $builder)
    {
        foreach ($this->config->getEnabledProviders() as $provider) {
            $name = strtolower($provider->getName());
            if ($name === 'local') {
                continue;
            }
            $builder->add(
                $name, ButtonType::class, [
                    'label' => $provider->getLabel(),
                    'attr'  => [
                        'class' => $this->getCssClass($name),
                        'href'  => '/authentication/login/process?provider=' . $provider->getName(),
                    ],
                ]
            );
        }
    }

    /**
     * Determine a button's CSS class
     *
     * @param string $name
     *
     * @return string
     */
    private function getCssClass($name)
    {
        return $this->config->getAddOn('zocial') ? "members-oauth-provider zocial $name" : "members-oauth-provider $name";
    }
}
