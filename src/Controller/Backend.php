<?php

namespace Bolt\Extension\Bolt\Members\Controller;

use Bolt\Controller\Zone;
use Bolt\Extension\Bolt\Members\MembersExtension;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Backend controller.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Backend implements ControllerProviderInterface
{
    /** @var array */
    private $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = $app['controllers_factory'];
        $ctr->value(Zone::KEY, Zone::BACKEND);

        $ctr->match('/extend/members', [$this, 'admin'])
            ->bind('MembersAdmin')
            ->method('GET')
        ;

        $ctr->before([$this, 'before']);

        return $ctr;
    }

    /**
     * Controller before render
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return RedirectResponse|null
     */
    public function before(Request $request, Application $app)
    {
        $user    = $app['users']->getCurrentUser();
        $userid  = $user['id'];

        foreach ($this->config['roles']['admin'] as $role) {
            if ($app['users']->hasRole($userid, $role)) {
                return null;
            }
        }

        /** @var UrlGeneratorInterface $generator */
        $generator = $app['url_generator'];

        return new RedirectResponse($generator->generate('dashboard'), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * The main admin page
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function admin(Application $app, Request $request)
    {
        $this->addTwigPath($app);

        $html = $app['render']->render('@MembersAdmin/members.twig', [
            'members' => $app['members.records']->getAccounts(),
            'roles'   => $app['members.roles']->getRoles(),
        ]);

        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }

    /**
     * Set our Twig template path
     *
     * @param Application $app
     */
    private function addTwigPath(Application $app)
    {
        /** @var MembersExtension $extension */
        $extension = $app['extensions']->get('Bolt/Members');
        $dir = sprintf('extensions://%s/templates/admin', $extension->getBaseDirectory()->getFullPath());

        $app['twig.loader.bolt_filesystem']->addPath($dir, 'MembersAdmin');
    }
}
