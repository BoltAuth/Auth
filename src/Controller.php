<?php

namespace Bolt\Extension\Bolt\Members;

use Maid\Maid;
use Silex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Bolt\Extension\Bolt\Members\Validator\Constraints\ValidUsername;

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

    public function getMemberNew(Request $request)
    {
        // Add assets to Twig path
        $this->addTwigPath();

        // Get session data we need from ClientLogin
        $redirect = $this->app['session']->get('pending');
        $userdata = $this->app['session']->get('clientlogin');
        $userdata = json_decode($userdata['providerdata'], true);

        $data = array();
        $form = $this->app['form.factory']
                        ->createBuilder('form', $data,  array('csrf_protection' => $this->config['csrf']))
                            ->add('username',    'text',   array('constraints' => new ValidUsername(),
                                                                 'data'  => makeSlug($userdata['displayName'], 32),
                                                                 'label' => __('User name:')))
                            ->add('displayname', 'text',   array('constraints' => new Assert\NotBlank(),
                                                                 'data'  => $userdata['displayName'],
                                                                 'label' => __('Publicly visible name:')))
                            ->add('email',       'text',   array('constraints' => new Assert\Email(array(
                                                                    'message' => 'The email "{{ value }}" is not a valid email.',
                                                                    'checkMX' => true)),
                                                                 'data'  => $userdata['email'],
                                                                 'label' => __('Email:')))
                            ->add('submit',      'submit', array('label' => __('Save & continue')))
                            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $submit = $request->get('form');

            if (true) {
                //
                $this->app['session']->getFlashBag()->set('error', 'The user name you chose already exists.');
            } else {
                // Create new Member record and go back to where we came from
                if ($this->members->newMember($submit)) {
                    // Redirect
                    if (empty($redirect)) {
                        $returnpage = str_replace($this->app['paths']['hosturl'], '', $redirect);
                        simpleredirect($returnpage);
                    } else {
                        simpleredirect($this->app['paths']['hosturl']);
                    }
                }
            }
        }

        $view = $form->createView();

        $html = $this->app['render']->render(
            $this->config['templates']['new'], array(
                'form' => $view,
                'twigparent' => $this->config['templates']['parent']
        ));

        return new \Twig_Markup($html, 'UTF-8');
    }

    public function getMemberProfile(Request $request)
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
