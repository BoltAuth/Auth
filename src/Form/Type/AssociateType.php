<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Form\MembersForms;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Associate social media type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AssociateType extends AbstractType
{
    use ProviderButtonsTrait;

    public function getName()
    {
        return MembersForms::ASSOCIATE;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addProviderButtons($builder, false);
    }
}
