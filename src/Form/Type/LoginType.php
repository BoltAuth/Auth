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
 * Login type
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',       EmailType::class,   [
                'label'       => Trans::__('Email:'),
                'data'        => isset($options['data']['email']) ? $options['data']['email'] : null,
                'constraints' => new Assert\Email([
                    'message' => 'The address "{{ value }}" is not a valid email.',
                    'checkMX' => true,
                ]),
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ])
            ->add('submit',      'submit', [
                'label'   => Trans::__('Login'),
            ]);
    }

    public function getName()
    {
        return 'login';
    }
}
