<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Form\Validator\Constraint\UniqueEmail;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User registration type
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class RegisterType extends AbstractType
{
    /** @var Records */
    private $records;

    /**
     * Constructor.
     *
     * @param Records $records
     * @param mixed   $options
     */
    public function __construct(Records $records, $options = null)
    {
        $this->records = $records;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayname', 'text',   [
                'label'       => Trans::__('Publicly visible name:'),
                'data'        => $options['data']['displayname'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2]),
                ],
            ])
            ->add('email',       'text',   [
                'label'       => Trans::__('Email:'),
                'data'        => $options['data']['email'],
                'constraints' => [
                    new UniqueEmail($this->records),
                    new Assert\Email([
                        'message' => 'The address "{{ value }}" is not a valid email.',
                        'checkMX' => true,
                    ]),
                ],
            ])
            ->add('submit',      'submit', [
                'label'       => Trans::__('Save & continue'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'register';
    }
}
