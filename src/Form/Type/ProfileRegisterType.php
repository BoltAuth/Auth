<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Extension\BoltAuth\Auth\Form\AuthForms;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User registration type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileRegisterType extends AbstractProfileType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AuthForms::PROFILE_REGISTER;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

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

        $builder
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
