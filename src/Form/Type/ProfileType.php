<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Profile type
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileType extends AbstractType
{
    /** @var boolean */
    protected $requirePassword = true;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayname', TextType::class,   [
                'label'       => Trans::__('Public name:'),
                'data'        => $options['data']['displayname'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2]),
                ],
            ])
            ->add('email',       EmailType::class,   [
                'label'       => Trans::__('Email:'),
                'data'        => $options['data']['email'],
                'constraints' => new Assert\Email([
                    'message' => 'The address "{{ value }}" is not a valid email.',
                    'checkMX' => true,
                ]),
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'empty_data'     => null,
                'required'       => $this->requirePassword,
            ])
            ->add('submit',      'submit', [
                'label'   => Trans::__('Save & continue'),
            ]);
    }

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
