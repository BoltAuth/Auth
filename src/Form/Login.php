<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Login form.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Login extends AbstractForm
{
    /** @var Type\LoginType */
    protected $type;
    /** @var Entity\Login */
    protected $entity;
    /** @var Request */
    protected $request;

    /**
     * @param Request $request
     *
     * @return Login
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
            ]
        ];
    }
}
