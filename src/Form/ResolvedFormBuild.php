<?php

namespace Bolt\Extension\BoltAuth\Auth\Form;

use Bolt\Extension\BoltAuth\Auth\Form\Builder\AuthFormBuilderInterface;
use Bolt\Extension\BoltAuth\Auth\Form\Entity\EntityInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Resolved form building class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ResolvedFormBuild
{
    /** @var AuthFormBuilderInterface[] */
    private $formBuilder;
    /** @var FormInterface[] */
    protected $form;
    /** @var array */
    protected $context;

    /**
     * Add a builder and form.
     *
     * @param string                      $formName
     * @param AuthFormBuilderInterface $formBuilder
     * @param FormInterface               $form
     *
     * @return ResolvedFormBuild
     */
    public function addBuild($formName, AuthFormBuilderInterface $formBuilder, FormInterface $form)
    {
        $this->formBuilder[$formName] = $formBuilder;
        $this->form[$formName] = $form;

        return $this;
    }

    /**
     * @param $formName
     *
     * @return AuthFormBuilderInterface
     */
    public function getFormBuilder($formName)
    {
        return $this->formBuilder[$formName];
    }

    /**
     * Return the Symfony Form object.
     *
     * @param string $formName
     *
     * @throws \BadMethodCallException
     *
     * @return FormInterface
     */
    public function getForm($formName)
    {
        if (!isset($this->form[$formName])) {
            $message = sprintf(
                'Requested form "%s" not part of this build. Available forms: %s',
                $formName,
                implode(', ', array_keys((array) $this->form))
            );
            throw new \BadMethodCallException($message);
        }

        return $this->form[$formName];
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
        $this->form[$formName] = $form;

        return $this;
    }

    /**
     * Return all the Symfony Form objects.
     *
     * @return FormInterface[]
     */
    public function getForms()
    {
        return $this->form;
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
     * @param string $formName
     *
     * @return FormTypeInterface
     */
    public function getType($formName)
    {
        return $this->formBuilder[$formName]->getType();
    }

    /**
     * @param string $formName
     *
     * @return EntityInterface
     */
    public function getEntity($formName)
    {
        return $this->formBuilder[$formName]->getEntity();
    }
}
