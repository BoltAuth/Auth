<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Password reset type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractProfileRecoveryType extends AbstractType
{
    /** @var boolean */
    protected $requirePassword = true;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label'       => Trans::__($this->config->getLabel('email')),
                    'attr'        => [
                        'placeholder' => $this->config->getPlaceholder('email'),
                    ],
                    'constraints' => [
                        new Assert\Email([
                            'message' => 'The address "{{ value }}" is not a valid email.',
                            'checkMX' => true,
                        ]),
                    ],
                ]
            )
        ;

        if ($this->requirePassword) {
            $passwordConstraints = [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 6]),
            ];

            $builder->add(
                'password',
                RepeatedType::class,
                [
                    'type'           => PasswordType::class,
                    'first_options'  => [
                        'label' => Trans::__($this->config->getLabel('password_first')),
                        'attr'  => [
                            'placeholder' => $this->config->getPlaceholder('password_first'),
                        ],
                        'constraints'     => $passwordConstraints,
                        'required'        => $this->requirePassword,
                    ],
                    'second_options'  => [
                        'label' => Trans::__($this->config->getLabel('password_second')),
                        'attr'  => [
                            'placeholder'     => $this->config->getPlaceholder('password_second'),
                            'required'        => $this->requirePassword,
                        ],
                        'constraints' => $passwordConstraints,
                    ],
                    'empty_data'      => null,
                ]
            );
        }

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label'   => Trans::__($this->config->getLabel('profile_save')),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'profile_recovery';
    }
}
