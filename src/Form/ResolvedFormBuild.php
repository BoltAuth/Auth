<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Form\Builder\MembersFormBuilderInterface;
use Bolt\Extension\Bolt\Members\Form\Entity\EntityInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Resolved form building class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ResolvedFormBuild
{
    /** @var MembersFormBuilderInterface */
    private $formBuilder;
    /** @var FormInterface[] */
    protected $forms;
    /** @var array */
    protected $context;
    /** @var FormTypeInterface */
    protected $type;
    /** @var EntityInterface */
    protected $entity;


    /**
     * Constructor.
     *
     * @param MembersFormBuilderInterface $formBuilder
     * @param FormInterface|null          $form
     * @param array|null                  $context
     * @param FormTypeInterface|null      $type
     * @param EntityInterface|null        $entity
     */
    public function __construct(FormInterface $form = null, array $context = null, FormTypeInterface $type = null, EntityInterface $entity = null)
    {
//$this->formBuilder = $formBuilder;
        if ($form) {
            $this->setForm($form);
        }
        $this->context = $context;
        $this->type = $type;
        $this->entity = $entity;
    }

    /**
     * @return MembersFormBuilderInterface
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }

    /**
     * Return the Symfony Form object.
     *
     * @param string $name
     *
     * @throws \BadMethodCallException
     *
     * @return FormInterface
     */
    public function getForm($name)
    {
        if (!isset($this->forms[$name])) {
            throw new \BadMethodCallException(sprintf('Form %s not found.', $name));
        }

        return $this->forms[$name];
    }

    /**
     * Add a form.
     *
     * @param FormInterface $form
     *
     * @return ResolvedFormBuild
     */
    public function setForm(FormInterface $form)
    {
        $formName = sprintf('form_%s', $form->getName());
        $this->forms[$formName] = $form;

        return $this;
    }

    /**
     * Return all the Symfony Form objects.
     *
     * @return FormInterface[]
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * Return the additional context parameters.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the context variable array.
     *
     * @param array $context
     *
     * @return ResolvedFormBuild
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return FormTypeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param FormTypeInterface $type
     *
     * @return ResolvedFormBuild
     */
    public function setType(FormTypeInterface $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param EntityInterface $entity
     *
     * @return ResolvedFormBuild
     */
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;

        return $this;
    }
}
