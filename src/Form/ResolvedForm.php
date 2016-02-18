<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Twig_Environment as TwigEnvironment;
use Twig_Markup as TwigMarkup;

/**
 * Resolved Form object class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ResolvedForm
{
    /** @var Form */
    protected $form;
    /** @var TwigEnvironment */
    protected $twig;
    /** @var array */
    protected $context = [];
    /** @var TwigMarkup */
    protected $renderedForm;

    /**
     * Constructor.
     *
     * @param FormInterface   $form
     * @param TwigEnvironment $twig
     */
    public function __construct(FormInterface $form, TwigEnvironment $twig)
    {
        $this->form = $form;
        $this->twig = $twig;
    }

    /**
     * Return the Symfony Form object.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Render the form and return the HTML.
     *
     * @param string $template
     *
     * @return TwigMarkup
     */
    public function getRenderedForm($template)
    {
        $html = $this->twig->render($template, $this->context);

        return $this->renderedForm = new TwigMarkup($html, 'UTF-8');
    }

    /**
     * Set the 'context' array to be passed to Twig during the render.
     *
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }
}
