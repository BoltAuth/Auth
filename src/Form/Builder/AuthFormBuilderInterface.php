<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Builder;

use Bolt\Extension\BoltAuth\Auth\Form\Entity\EntityInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Auth form builder interface.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
interface AuthFormBuilderInterface
{
    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param FormTypeInterface    $type        The type of the form
     * @param EntityInterface      $entity      The initial entity data
     */
    public function __construct(FormFactoryInterface $formFactory, FormTypeInterface $type, EntityInterface $entity);

    /**
     * Create the form.
     *
     * @param array $options Form options
     *
     * @throws \RuntimeException
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm(array $options);

    /**
     * Return the form object.
     *
     * @throws \RuntimeException
     *
     * @return Form
     */
    public function getForm();

    /**
     * Return the type object in use.
     *
     * @return FormTypeInterface
     */
    public function getType();

    /**
     * Return the entity object in use.
     *
     * @return EntityInterface
     */
    public function getEntity();
}
