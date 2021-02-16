<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Extension\BoltAuth\Auth\Form\AuthForms;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Associate social media type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AssociateType extends AbstractType
{
    use ProviderButtonsTrait;

    public function getName()
    {
        return AuthForms::ASSOCIATE;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addProviderButtons($builder, false);
    }
}
