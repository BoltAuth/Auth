<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

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
class ProfileRecoveryType extends AbstractType
{
    /** @var boolean */
    protected $requirePassword = true;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->requirePassword) {
            $passwordConstraints = [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 6]),
            ];
        } else {
            $passwordConstraints = [];
        }

        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label'       => Trans::__($this->config->getLabel('email')),
                    'data'        => $this->getData($options, 'email'),
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
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type'           => PasswordType::class,
                    'first_options'  => [
                        'label' => Trans::__($this->config->getLabel('password_first')),
                        'attr'  => [
                            'placeholder' => $this->config->getPlaceholder('password_first'),
                        ],
                        'constraints' => $passwordConstraints,
                    ],
                    'second_options'  => [
                        'label' => Trans::__($this->config->getLabel('password_second')),
                        'attr'  => [
                            'placeholder' => $this->config->getPlaceholder('password_second'),
                        ],
                        'constraints' => $passwordConstraints,
                    ],
                    'empty_data'      => null,
                    'required'        => $this->requirePassword,
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label'   => Trans::__($this->config->getLabel('profile_save')),
                ]
            )
        ;
    }

    public function getName()
    {
        return 'profile_recovery';
    }

    /**
     * @param boolean $requirePassword
     *
     * @return ProfileEditType
     */
    public function setRequirePassword($requirePassword)
    {
        $this->requirePassword = $requirePassword;

        return $this;
    }
}
