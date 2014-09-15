<?php

namespace Bolt\Extension\Bolt\Members;

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

    public function getMemberRegister(Request $request)
    {
        // Add assets to Twig path
        $this->addTwigPath();

        // Get redirect that is set for ClientLogin
        $redirect = $this->app['session']->get('pending');

        // Get session data we need from ClientLogin
        $clientlogin = $this->app['session']->get('clientlogin');

        // Expand the JSON array
        $userdata = json_decode($clientlogin['providerdata'], true);

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
                                                                    'message' => 'The address "{{ value }}" is not a valid email.',
                                                                    'checkMX' => true)),
                                                                 'data'  => $userdata['email'],
                                                                 'label' => __('Email:')))
                            ->add('provider',    'hidden', array('data'  => $clientlogin['provider']))
                            ->add('identifier',  'hidden', array('data'  => $clientlogin['identifier']))
                            ->add('submit',      'submit', array('label' => __('Save & continue')))
                            ->getForm();

        // Handle the form request data
        $form->handleRequest($request);

        // If we're in a POST, validate the form
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                // Create new Member record and go back to where we came from
                if ($this->members->addMember($request->get('form'), $userdata)) {
                    // Clear any redirect that ClientLogin has pending
                    $this->app['session']->remove('pending');
                    $this->app['session']->remove('clientlogin');

                    // Redirect
                    if (empty($redirect)) {
                        simpleredirect($this->app['paths']['hosturl']);
                    } else {
                        $returnpage = str_replace($this->app['paths']['hosturl'], '', $redirect);
                        simpleredirect($returnpage);
                    }
                } else {
                    // Something is wrong here
                }
            }
        }

        $view = $form->createView();

        $html = $this->app['render']->render(
            $this->config['templates']['register'], array(
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
