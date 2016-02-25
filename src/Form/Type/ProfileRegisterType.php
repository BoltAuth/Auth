<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Form\Validator\Constraint\UniqueEmail;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User registration type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileRegisterType extends AbstractType
{
    /** @var Records */
    protected $records;
    /** @var Session */
    protected $session;

    /**
     * Constructor.
     *
     * @param Config  $config
     * @param Records $records
     */
    public function __construct(Config $config, Records $records, Session $session)
    {
        parent::__construct($config);
        $this->records = $records;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->session->isTransitional() === false) {
            $passwordConstraints = [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 6]),
            ];
        } else {
            $passwordConstraints = [];
        }
        $builder
            ->add(
                'displayname',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getLabel('displayname')),
                    'data'        => $this->getData($options, 'displayname'),
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
                    'data'        => $this->getData($options, 'email'),
                    'attr'        => [
                        'placeholder' => $this->config->getPlaceholder('email'),
                    ],
                    'constraints' => [
                        new UniqueEmail($this->records),
                        new Assert\Email([
                            'message' => 'The address "{{ value }}" is not a valid email.',
                            'checkMX' => true,
                        ]),
                    ],
                ]
            )
            ->add(
                'plainPassword',
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
                    'required'        => !$this->session->isTransitional(),
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'register';
    }
}
