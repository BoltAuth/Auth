<?php

namespace Bolt\Extension\BoltAuth\Auth\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Form section configuration class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Forms extends ParameterBag
{
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);

        $this->set('addons', new ParameterBag($parameters['addons']));
        $this->set('labels', new ParameterBag($parameters['labels']));
        $this->set('placeholders', new ParameterBag($parameters['placeholders']));

        $templates = new ParameterBag();
        foreach ($parameters['templates'] as $type => $values) {
            if (isset($values['body'])) {
                // BC for splitting up HTML & text email templates
                $parameters['templates'][$type]['html'] = $values['body'];
                unset($parameters['templates'][$type]['body']);
            }
            $templates->set($type, new ParameterBag($parameters['templates'][$type]));
        }
        $this->set('templates', $templates);
    }

    /**
     * @return ParameterBag
     */
    public function getTemplates()
    {
        return $this->get('templates');
    }

    /**
     * @return ParameterBag
     */
    public function getLabels()
    {
        return $this->get('labels');
    }

    /**
     * @return ParameterBag
     */
    public function getPlaceholders()
    {
        return $this->get('placeholders');
    }

    /**
     * @return ParameterBag
     */
    public function getAddOns()
    {
        return $this->get('addons');
    }
}
