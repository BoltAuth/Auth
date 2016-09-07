<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * OAuth login type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class LoginOauthType extends AbstractType
{
    use ProviderButtonsTrait;

    public function getName()
    {
        return 'login_oauth';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addProviderButtons($builder, true);
    }
}
