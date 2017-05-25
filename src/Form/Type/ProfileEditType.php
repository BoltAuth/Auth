<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Form\MembersForms;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Profile type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileEditType extends AbstractProfileType
{
    /** @var boolean */
    protected $requirePassword = true;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return MembersForms::PROFILE_EDIT;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

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
