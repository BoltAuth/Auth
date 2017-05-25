<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Extension\BoltAuth\Auth\Form\AuthForms;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * OAuth login type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class LoginOauthType extends AbstractType
{
    use ProviderButtonsTrait;

    public function getName()
    {
        return AuthForms::LOGIN_OAUTH;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addProviderButtons($builder, true);
    }
}
