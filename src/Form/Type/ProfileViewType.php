<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Extension\BoltAuth\Auth\Form\AuthForms;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Profile view type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileViewType extends AbstractProfileType
{
    /** @var boolean */
    protected $requirePassword = false;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AuthForms::PROFILE_VIEW;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('read_only', true);
    }
}
