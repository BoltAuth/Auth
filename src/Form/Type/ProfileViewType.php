<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Form\MembersForms;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Profile view type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
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
        return MembersForms::PROFILE_VIEW;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('read_only', true);
    }
}
