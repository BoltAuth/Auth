<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type;
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
class LoginPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',       Type\EmailType::class,   [
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
            ->add('password', Type\PasswordType::class, [
                'label'       => Trans::__($this->config->getLabel('password_first')),
                'data'        => null,
                'attr'        => [
                    'placeholder' => $this->config->getPlaceholder('password_first'),
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 6]),
                ],
            ])
            ->add('submit',   Type\SubmitType::class, [
                'label'   => Trans::__($this->config->getLabel('login')),
            ])
        ;
    }

    public function getName()
    {
        return 'login_password';
    }
}
