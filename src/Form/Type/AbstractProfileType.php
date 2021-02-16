<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base profile type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractProfileType extends AbstractType
{
    /** @var boolean */
    protected $requireEmail = true;
    /** @var boolean */
    protected $requirePassword = true;

    /**
     * Enable or disable requiring email address field and constraints.
     *
     * @param boolean $requireEmail
     *
     * @return AbstractProfileType
     */
    public function setRequireEmail($requireEmail)
    {
        $this->requireEmail = $requireEmail;

        return $this;
    }

    /**
     * Enable or disable requiring password fields and constraints.
     *
     * @param boolean $requirePassword
     *
     * @return AbstractProfileType
     */
    public function setRequirePassword($requirePassword)
    {
        $this->requirePassword = $requirePassword;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $passwordConstraints = [];
        if ($this->requirePassword) {
            $passwordConstraints = [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 6]),
            ];
        }
        $emailConstraints = [];
        if ($this->requireEmail) {
            $emailConstraints = [
                new Assert\Email([
                    'message' => 'The address "{{ value }}" is not a valid email.',
                    'checkMX' => true,
                ]),
            ];
        }

        $builder
            ->add(
                'displayname',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getLabel('displayname')),
                    'attr'        => [
                        'placeholder' => $this->config->getPlaceholder('displayname'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(['min' => 2]),
                    ],
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label'       => Trans::__($this->config->getLabel('email')),
                    'attr'        => [
                        'placeholder' => $this->config->getPlaceholder('email'),
                    ],
                    'constraints' => $emailConstraints,
                    'required'    => $this->requireEmail,
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
}
