<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addProviderButtons($builder, false);
    }

    public function getName()
    {
        return 'associate';
    }
}
