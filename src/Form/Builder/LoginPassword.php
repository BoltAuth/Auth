<?php

namespace Bolt\Extension\Bolt\Members\Form\Builder;

use Bolt\Extension\Bolt\Members\Form\Entity;
use Bolt\Extension\Bolt\Members\Form\Type;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Login form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class LoginPassword extends AbstractFormBuilder
{
    /** @var Type\LoginPasswordType */
    protected $type;
    /** @var Entity\Profile */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function getData(Storage\Records $records)
    {
        if ($this->request === null) {
            throw new \RuntimeException('Request has not been set.');
        }

        $this->entity->setEmail($this->request->request->get('email'));

        return [
            'data' => $this->entity,
        ];
    }
}
