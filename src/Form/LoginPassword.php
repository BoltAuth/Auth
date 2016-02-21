<?php

namespace Bolt\Extension\Bolt\Members\Form;

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
class LoginPassword extends AbstractForm
{
    /** @var Type\LoginPasswordType */
    protected $type;
    /** @var Entity\LoginPassword */
    protected $entity;
    /** @var Request */
    protected $request;

    /**
     * @param Request $request
     *
     * @return LoginPassword
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

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
        if ($this->request === null) {
            throw new \RuntimeException('Request has not been set.');
        }

        return [
            'csrf_protection' => true,
            'data'            => [
                'email' => $this->request->request->get('email'),
            ],
        ];
    }
}
