<?php

namespace Bolt\Extension\Bolt\Members\Form\Builder;

use Bolt\Extension\Bolt\Members\Form\Entity\EntityInterface;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Abstract Form class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractFormBuilder implements MembersFormBuilderInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var FormTypeInterface */
    protected $type;
    /** @var EntityInterface */
    protected $entity;
    /** @var Form */
    protected $form;
    /** @var string */
    protected $action;

    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param FormTypeInterface    $type
     * @param EntityInterface      $entity
     */
    public function __construct(FormFactoryInterface $formFactory, FormTypeInterface $type, EntityInterface $entity)
    {
        $this->formFactory = $formFactory;
        $this->type = $type;
        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function createForm(Storage\Records $records)
    {
        $data = $this->getData($records);
        if ($data === null) {
            throw new \RuntimeException('Form data not set.');
        }

        $builder = $this->formFactory->createBuilder($this->type, $this->entity, $data);
        if ($this->action !== null) {
            $builder->setAction($this->action);
        }

        return $this->form = $builder->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        if ($this->form === null) {
            throw new \RuntimeException('Form has not been created.');
        }

        return $this->form;
    }

    /**
     * Set the HTML 'action' URL for a form's POST target URL.
     *
     * @param string $action
     *
     * @return AbstractFormBuilder
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @deprecated
     *
     * {@inheritdoc}
     */
    public function saveForm(Storage\Records $records, EventDispatcherInterface $eventDispatcher)
    {
        return $this;
    }

    /**
     * Return the form data array.
     *
     * @param Storage\Records $records
     *
     * @return array
     */
    abstract protected function getData(Storage\Records $records);
}
