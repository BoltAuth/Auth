<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Extension\BoltAuth\Auth\Form\AuthForms;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Logout type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class LogoutType extends AbstractType
{
    public function getName()
    {
        return AuthForms::LOGOUT;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'logout',
                ButtonType::class,
                [
                    'label'   => Trans::__($this->config->getLabel('logout')),
                    'attr'    => [
                        'class' => 'auth-logout-button',
                        'href'  => sprintf('/%s/logout', $this->config->getUrlAuthenticate()),
                    ],
                ]
            )
        ;
    }
}
