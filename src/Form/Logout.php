<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Logout form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Logout extends AbstractForm
{
    /** @var Type\LoginType */
    protected $type;
    /** @var Entity\Login */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    public function saveForm(Storage\Records $records, EventDispatcherInterface $eventDispatcher)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(Storage\Records $records)
    {
        return [
            'csrf_protection' => true,
        ];
    }
}
