<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayname', 'text',   [
                'label'       => Trans::__('Public name:'),
                'data'        => $options['data']['displayname'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2]),
                ],
            ])
            ->add('email',       'text',   [
                'label'       => Trans::__('Email:'),
                'data'        => $options['data']['email'],
                'constraints' => new Assert\Email([
                    'message' => 'The address "{{ value }}" is not a valid email.',
                    'checkMX' => true,
                ]),
            ])
            ->add('submit',      'submit', [
                'label'   => Trans::__('Save & continue'),
            ]);
    }

    public function getName()
    {
        return 'profile';
    }
}
