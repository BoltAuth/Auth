<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Config\Config;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Provider buttons trait.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
trait ProviderButtonsTrait
{
    /**
     * Add any configured provider buttons to the form.
     *
     * @param FormBuilderInterface $builder
     * @param bool                 $isLogin
     */
    protected function addProviderButtons(FormBuilderInterface $builder, $isLogin)
    {
        foreach ($this->getConfig()->getEnabledProviders() as $provider) {
            $name = strtolower($provider->getName());
            if ($name === 'local') {
                continue;
            }
            $builder->add(
                $name,
                SubmitType::class,
                [
                    'label' => $isLogin ? $provider->getLabelSignIn() : $provider->getLabelAssociate(),
                    'attr'  => [
                        'class' => $this->getCssClass($name),
                    ],
                ]
            );
        }
    }

    /**
     * Determine a button's CSS class
     *
     * @param string $name
     *
     * @return string
     */
    protected function getCssClass($name)
    {
        return $this->getConfig()->getAddOn('zocial') ? "members-oauth-provider zocial $name" : "members-oauth-provider $name";
    }

    /**
     * @return Config
     */
    abstract protected function getConfig();
}
