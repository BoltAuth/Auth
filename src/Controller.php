<?php

namespace Bolt\Extension\Bolt\Members;

use Silex;

/**
 *
 */
class Controller
{
    /**
     * @var Silex\Application
     */
    private $app;

    /**
     * Extension config array
     *
     * @var array
     */
    private $config;

    /**
     * @var Members
     */
    private $members;

    public function __construct(Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['extensions.' . Extension::NAME]->config;
        $this->members = new Members($this->app);
    }

    public function getMemberNew()
    {
        // Add assets to Twig path
        $this->addTwigPath();

        $view = '';

        $html = $this->app['render']->render(
            $this->config['templates']['new'], array(
                'form' => $view,
                'twigparent' => $this->config['templates']['parent']
        ));

        return new \Twig_Markup($html, 'UTF-8');
    }

    public function getMemberProfile()
    {
        // Add assets to Twig path
        $this->addTwigPath();

        $view = '';

        $html = $this->app['render']->render(
            $this->config['templates']['profile'], array(
                'form' => $view,
                'twigparent' => $this->config['templates']['parent']
        ));

        return new \Twig_Markup($html, 'UTF-8');
    }

    private function addTwigPath()
    {
        $this->app['twig.loader.filesystem']->addPath(dirname(__DIR__) . '/assets');
    }

}
