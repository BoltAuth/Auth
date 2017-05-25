<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

use Bolt\Extension\Bolt\Members\Config\Config;
use Symfony\Component\Form\AbstractType as BaseAbstractType;

/**
 * Base class for form types.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
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
     * @internal For use by traits.
     *
     * @return Config
     */
    protected function getConfig()
    {
        return $this->config;
    }
}
