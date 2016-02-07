<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Form\Entity\EntityInterface;
use Bolt\Extension\Bolt\Members\Storage;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Abstract Form class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractForm implements MembersFormInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var FormTypeInterface */
    protected $type;
    /** @var EntityInterface */
    protected $entity;
    /** @var Form */
    protected $form;

    public function __construct(FormFactoryInterface $formFactory, FormTypeInterface $type, EntityInterface $entity)
    {
        $this->formFactory = $formFactory;
        $this->type = $type;
        $this->entity = $entity;
    }

    /**
     * Create the form.
     *
     * @param Storage\Records $records
     *
     * @throws \RuntimeException
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm(Storage\Records $records)
    {
        $data = $this->getData($records);
        if ($data === null) {
            throw new \RuntimeException('Form data not set.');
        }

        $builder = $this->formFactory->createBuilder(
            $this->type,
            $this->entity,
            $data
        );

        return $this->form = $builder->getForm();
    }

    /**
     * Return the form object.
     *
     * @throws \RuntimeException
     *
     * @return Form
     */
    public function getForm()
    {
        if ($this->form === null) {
            throw new \RuntimeException('Form has not been created.');
        }

        return $this->form;
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
