<?php

namespace Bolt\Extension\BoltAuth\Auth\Event;

use Bolt\Extension\BoltAuth\Auth\Form\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Event fired prior to a form's construction.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class FormBuilderEvent extends Event
{
    const BUILD = 'auth.form.construction';

    /** @var string */
    protected $name;
    /** @var FormTypeInterface */
    protected $type;
    /** @var  EntityInterface */
    protected $entity;
    /** @var  string */
    protected $entityClass;

    /**
     * Constructor.
     *
     * @param string            $name
     * @param FormTypeInterface $type
     * @param EntityInterface   $entity
     */
    public function __construct($name, FormTypeInterface $type = null, EntityInterface $entity = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return FormBuilderEvent
     */
    public function setType($type)
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
     * @return FormBuilderEvent
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     *
     * @return FormBuilderEvent
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }
}
