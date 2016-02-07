<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Form\Entity\EntityInterface;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Members form interface.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
interface MembersFormInterface
{
    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param FormTypeInterface    $type
     * @param EntityInterface      $entity
     */
    public function __construct(FormFactoryInterface $formFactory, FormTypeInterface $type, EntityInterface $entity);

    /**
     * Create the form.
     *
     * @param Storage\Records $records
     *
     * @throws \RuntimeException
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm(Storage\Records $records);

    /**
     * Return the form object.
     *
     * @throws \RuntimeException
     *
     * @return Form
     */
    public function getForm();

    /**
     * Save the form.
     *
     * @param Storage\Records          $records
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return MembersFormInterface
     */
    public function saveForm(Storage\Records $records, EventDispatcherInterface $eventDispatcher);
}
