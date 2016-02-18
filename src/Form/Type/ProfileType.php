<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Profile type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileType extends AbstractType
{
    /** @var Config */
    private $config;
    /** @var boolean */
    protected $requirePassword = true;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayname', TextType::class,   [
                'label'       => Trans::__($this->config->getLabel('displayname')),
                'data'        => $this->getData($options, 'displayname'),
                'attr'        => [
                    'placeholder' => $this->config->getPlaceholder('displayname'),
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2]),
                ],
            ])
            ->add('email',       EmailType::class,   [
                'label'       => Trans::__($this->config->getLabel('email')),
                'data'        => $this->getData($options, 'email'),
                'attr'        => [
                    'placeholder' => $this->config->getPlaceholder('email'),
                ],
                'constraints' => new Assert\Email([
                    'message' => 'The address "{{ value }}" is not a valid email.',
                    'checkMX' => true,
                ]),
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'first_options'  => [
                    'label'       => Trans::__($this->config->getLabel('password_first')),
                    'attr'        => [
                        'placeholder' => $this->config->getPlaceholder('password_first'),
                    ],
                ],
                'second_options' => [
                    'label'       => Trans::__($this->config->getLabel('password_second')),
                    'attr'        => [
                        'placeholder' => $this->config->getPlaceholder('password_second'),
                    ],
                ],
                'empty_data'     => null,
                'required'       => $this->requirePassword,
            ])
            ->add('submit',      SubmitType::class, [
                'label'   => Trans::__($this->config->getLabel('profile_save')),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'profile';
    }

    /**
     * @param boolean $requirePassword
     *
     * @return ProfileType
     */
    public function setRequirePassword($requirePassword)
    {
        $this->requirePassword = $requirePassword;

        return $this;
    }
}
