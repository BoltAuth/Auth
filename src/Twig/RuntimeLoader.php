<?php

namespace Bolt\Extension\Bolt\Members\Twig;

use Pimple as Container;
use Twig_RuntimeLoaderInterface as RuntimeLoaderInterface;

/**
 * Twig RuntimeLoader implementation.
 *
 * @internal for supporting Bolt < 3.3.0.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class RuntimeLoader implements RuntimeLoaderInterface
{
    /** @var Container */
    private $container;
    /** @var array */
    private $mapping;

    /**
     * Constructor.
     *
     * @param Container $container
     * @param array     $mapping
     */
    public function __construct(Container $container, array $mapping)
    {
        $this->container = $container;
        $this->mapping = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function load($class)
    {
        if (!isset($this->mapping[$class])) {
            return null;
        }

        return $this->container[$this->mapping[$class]];
    }
}
