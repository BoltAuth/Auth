<?php

namespace Bolt\Extension\Bolt\Members\Form\Builder;

use Bolt\Extension\Bolt\Members\Form\Entity;
use Bolt\Extension\Bolt\Members\Form\Type;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Social media account association form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Associate extends AbstractFormBuilder
{
    /** @var Type\AssociateType */
    protected $type;
    /** @var Entity\Profile */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function getData(Storage\Records $records)
    {
        return [
            'csrf_protection' => false,
        ];
    }
}
