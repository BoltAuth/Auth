<?php

namespace Bolt\Extension\Bolt\Members;

/**
 * Twig functions
 */
class MembersTwigExtension extends \Twig_Extension
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Bolt\Extension\Bolt\Members\Members
     */
    private $members;

    /**
     * @var \Twig_Environment
     */
    private $twig = null;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['extensions.' . Extension::NAME]->config;
        $this->members = new Members($app);
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twig = $environment;
    }

    /**
     * Return the name of the extension
     */
    public function getName()
    {
        return 'members.twig';
    }

    /**
     * The functions we add
     */
    public function getFunctions()
    {
        return array(
            'member'     => new \Twig_Function_Method($this, 'member'),
            'memberauth' => new \Twig_Function_Method($this, 'memberAuth'),
        );
    }

    /**
     * Return Twig suitable array for a member, or current session
     *
     * @param  integer      $id   [Optional] ID of member to look up
     * @param  boolean      $meta [Optional] Return user meta
     * @return \Twig_Markup
     */
    public function member($id = false, $meta = false)
    {
        $member = $this->members->getMember('id', $id);

        if ($meta) {
            $member['meta'] = $this->members->getMemberMeta($id);
        }

        return new \Twig_Markup($member, 'UTF-8');
    }

    public function memberAuth()
    {
        return $this->members->isAuth();
    }

}
