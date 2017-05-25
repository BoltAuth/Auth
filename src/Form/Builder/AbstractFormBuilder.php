<?php

namespace Bolt\Extension\Bolt\Members\Form\Builder;

use Bolt\Extension\Bolt\Members\Form\Entity\EntityInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Abstract Form class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
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
     * {@inheritdoc}
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
    public function createForm(array $options)
    {
        $builder = $this->formFactory->createBuilder($this->type, $this->entity, $options);
        if ($this->action !== null) {
            $builder->setAction($this->action);
        }
        $this->form = $builder->getForm();

        return $this->form;
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
     * @return FormTypeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
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
}
