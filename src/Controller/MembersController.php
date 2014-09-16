<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Silex;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Bolt\Extension\Bolt\Members\Extension;
use Bolt\Extension\Bolt\Members\Members;
use Bolt\Extension\Bolt\Members\Validator\Constraints\ValidUsername;

/**
 *
 */
class MembersController implements ControllerProviderInterface
{
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

    /**
     *
     * @param Silex\Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Silex\Application $app)
    {
        $this->config = $app['extensions.' . Extension::NAME]->config;
        $this->members = new Members($app);

        /**
         * @var $ctr \Silex\ControllerCollection
         */
        $ctr = $app['controllers_factory'];

        // New member
        $ctr->match('/register', array($this, 'register'))
            ->bind('register')
            ->method('GET|POST');

        // My profile
        $ctr->match('/profile', array($this, 'myprofile'))
            ->bind('myprofile')
            ->method('GET|POST');

        // Member profile
        $ctr->match('/profile/{id}', array($this, 'userprofile'))
            ->bind('userprofile')
            ->assert('id', '\d*')
            ->method('GET');

        return $ctr;
    }

    /**
     *
     * @param Silex\Application $app
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return \Twig_Markup
     */
    public function register(Silex\Application $app, Request $request)
    {
        // Add assets to Twig path
        $this->addTwigPath($app);

        // Get redirect that is set for ClientLogin
        $redirect = $app['session']->get('pending');

        // Get session data we need from ClientLogin
        $clientlogin = $app['session']->get('clientlogin');

        // If there is no ClientLogin data in the session, they shouldn't be here
        if (empty($clientlogin)) {
            return new Response('No!', Response::HTTP_FORBIDDEN, array('content-type' => 'text/html'));
        }

        // Expand the JSON array
        $userdata = json_decode($clientlogin['providerdata'], true);

        $data = array();
        $form = $app['form.factory']
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
                    $app['session']->remove('pending');
                    $app['session']->remove('clientlogin');

                    // Redirect
                    if (empty($redirect)) {
                        simpleredirect($app['paths']['hosturl']);
                    } else {
                        $returnpage = str_replace($app['paths']['hosturl'], '', $redirect);
                        simpleredirect($returnpage);
                    }
                } else {
                    // Something is wrong here
                }
            }
        }

        $view = $form->createView();

        $html = $app['render']->render(
            $this->config['templates']['register'], array(
                'form' => $view,
                'twigparent' => $this->config['templates']['parent']
        ));

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     *
     * @param Silex\Application $app
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return \Twig_Markup
     */
    public function myprofile(Silex\Application $app, Request $request)
    {
        // Add assets to Twig path
        $this->addTwigPath($app);

        $view = '';

        $html = $app['render']->render(
            $this->config['templates']['profile'], array(
                'form' => $view,
                'twigparent' => $this->config['templates']['parent']
        ));

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     *
     * @param Silex\Application $app
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return \Twig_Markup
     */
    public function userprofile(Silex\Application $app, Request $request)
    {
        // Add assets to Twig path
        $this->addTwigPath($app);

        $view = '';

        $html = $app['render']->render(
            $this->config['templates']['profile'], array(
                'form' => $view,
                'twigparent' => $this->config['templates']['parent']
        ));

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     *
     * @param Silex\Application $app
     */
    private function addTwigPath(Silex\Application $app)
    {
        $app['twig.loader.filesystem']->addPath(dirname(dirname(__DIR__)) . '/assets');
    }

}
