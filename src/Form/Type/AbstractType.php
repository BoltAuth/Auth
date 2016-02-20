<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Config\Config;
use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base class for form types.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AbstractType extends BaseAbstractType
{
    /** @var Config */
    protected $config;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Return a valid data option.
     *
     * @param array  $options
     * @param string $field
     *
     * @return mixed|null
     */
    protected function getData(array $options, $field)
    {
        if (!isset($options['data'])) {
            return null;
        }

        return isset($options['data'][$field]) ? $options['data'][$field] : null;
    }

    /**
     * Add any configured provider buttons to the form.
     *
     * @param FormBuilderInterface $builder
     */
    protected function addProviderButtons(FormBuilderInterface $builder)
    {
        foreach ($this->config->getEnabledProviders() as $provider) {
            $name = strtolower($provider->getName());
            if ($name === 'local') {
                continue;
            }
            $builder->add(
                $name, ButtonType::class, [
                    'label' => $provider->getLabelSignIn(),
                    'attr'  => [
                        'class' => $this->getCssClass($name),
                        'href'  => sprintf('/%s/login/process?provider=%s', $this->config->getUrlAuthenticate(), $provider->getName()),
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
        return $this->config->getAddOn('zocial') ? "members-oauth-provider zocial $name" : "members-oauth-provider $name";
    }
}
