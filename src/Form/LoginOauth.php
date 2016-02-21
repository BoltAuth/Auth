<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * OAuth login form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class LoginOauth extends AbstractForm
{
    /** @var Type\LoginOauthType */
    protected $type;
    /** @var Entity\LoginOauth */
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
