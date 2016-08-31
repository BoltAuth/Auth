<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Symfony\Component\Form\FormInterface;

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
    /** @var FormInterface[] */
    protected $forms;
    /** @var array */
    protected $context;

    /**
     * Constructor.
     *
     * @param FormInterface[] $forms
     * @param array           $context
     */
    public function __construct(array $forms, array $context)
    {
        /** @var FormInterface $form */
        foreach ($forms as $form) {
            if (!$form instanceof FormInterface) {
                throw new \BadMethodCallException('Object does not implement %s', FormInterface::class);
            }
            $formName = sprintf('form_%s', $form->getName());
            $this->forms[$formName] = $form;
        }
        $this->context = $context;
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
}
