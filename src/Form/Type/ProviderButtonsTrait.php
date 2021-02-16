<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Type;

use Bolt\Extension\BoltAuth\Auth\Config\Config;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Provider buttons trait.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
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
        return $this->getConfig()->getAddOn('zocial') ? "auth-oauth-provider zocial $name" : "auth-oauth-provider $name";
    }

    /**
     * @return Config
     */
    abstract protected function getConfig();
}
