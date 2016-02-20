<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Logout type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class LogoutType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('logout',   ButtonType::class, [
                'label'   => Trans::__($this->config->getLabel('logout')),
                'attr'    => [
                    'class' => 'members-logout-button',
                    'href'  => sprintf('/%s/logout', $this->config->getUrlAuthenticate()),
                ],
            ])
        ;
    }

    public function getName()
    {
        return 'logout';
    }
}
