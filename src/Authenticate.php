<?php

namespace Bolt\Extension\Bolt\Members;

use Bolt\Extension\Bolt\ClientLogin\Authorisation\SessionManager;
use Bolt\Extension\Bolt\ClientLogin\Event\ClientLoginEvent;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\SessionToken;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Member authentication interface class
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Authenticate implements EventSubscriberInterface
{
    /** @var Application */
    private $app;
    /** @var Records */
    private $records;
    /** @var array */
    private $config;
    /** @var  SessionManager */
    private $clientSession;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param array       $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->records = $app['members.records'];
        $this->clientSession = $app['clientlogin.session'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ClientLoginEvent::LOGIN_POST  => ['login'],
            ClientLoginEvent::LOGOUT_POST => ['logout'],
        ];
    }

    /**
     * Authentication login processing
     *
     * @param ClientLoginEvent $event
     */
    public function login(ClientLoginEvent $event)
    {
        /** @var SessionToken */
        $sessionToken = $event->getSessionToken();

        // See if we have this in our database
        $member = $this->records->getMemberByProviderId($sessionToken->getGuid());

        if ($member) {
            $this->updateMemberLogin($member);
        } else {
            // If registration is closed, don't do anything
            if (!$this->config['registration']) {
                // @TODO handle this properly
                return;
            }

            // Save any redirect that ClientLogin has pending
            $this->app['session']->set('pending',     $this->app['request']->get('redirect'));
            $this->app['session']->set('clientlogin', $sessionToken->getGuid());

            // Check to see if there is already a member with this email
            $member = $this->app['members']->getMember('email', $sessionToken->email);

            if ($member) {
                // Associate this login with their Members profile
                $this->addMemberProvider($member['guid '], $sessionToken->provider, $sessionToken->uid);
            } else {
                // Redirect to the 'new' page
                $redirect = sprintf('/%s/register', $this->config['basepath']);
                $this->app['session']->set('redirect', new RedirectResponse($redirect));
            }
        }
    }

    /**
     * Authentication logout processing
     *
     * @param ClientLoginEvent $event
     */
    public function logout(ClientLoginEvent $event)
    {
    }

    /**
     * Test if a user has a valid ClientLogin session AND is a valid member
     *
     * @return boolean|integer Member ID, or false
     */
    public function getMember()
    {
        // First check for ClientLogin auth
        if (!$this->clientSession->isLoggedIn()) {
            return false;
        }

        // Get their ClientLogin records
        $sessionToken = $this->clientSession->getLoggedIn();
        if ($sessionToken === null) {
            throw new \RuntimeException('Unable to retrieve ClientLogin session.');
        }

        // Look them up internally
        return $this->records->getMemberByProviderId($sessionToken->getGuid());
    }

    /**
     * Add a ClientLogin key to a user's profile
     *
     * @param integer $userid     A user's ID
     * @param string  $provider   The login provider
     * @param string  $identifier Provider's unique ID for the user
     *
     * @return bool
     */
    private function addMemberProvider($userid, $provider, $identifier)
    {
        if ($this->records->getMember('guid', $userid)) {
            $key = 'clientlogin_id_' . strtolower($provider);
            $this->records->updateMemberMeta($userid, $key, $identifier);

            return true;
        }

        return false;
    }

    /**
     * Add a new member to the database
     *
     * @param array  $form
     * @param Client $userdata The user data from ClientLogin
     *
     * @return boolean
     */
    protected function addMember($form, Client $userdata)
    {
        // Remember to look up email address and match new ClientLogin profiles
        // with existing Members

        $member = $this->app['members']->getMember('email', $form['email']);

        if ($member) {
            // We already have them, just link the profile
            $this->addMemberProvider($member['id'], $userdata->provider, $userdata->uid);
        } else {
            //
            $create = $this->records->updateMember(false, [
                'username'    => $form['username'],
                'email'       => $form['email'],
                'displayname' => $form['displayname'],
                'lastseen'    => date('Y-m-d H:i:s'),
                'lastip'      => $this->app['request']->getClientIp(),
                'enabled'     => 1,
            ]);

            if ($create) {
                // Get the new record
                $member = $this->app['members']->getMember('email', $form['email']);

                // Add the provider info to meta
                $this->addMemberProvider($member['id'], $userdata->provider, $userdata->uid);

                // Add meta data from ClientLogin
                $this->records->updateMemberMeta($member['id'], 'avatar', $userdata->imageUrl);

                // Event dispatcher
                if ($this->app['dispatcher']->hasListeners('members.New')) {
                    $event = new Event\MembersEvent();
                    $this->app['dispatcher']->dispatch('members.New', $event);
                }
            }
        }

        return true;
    }

    /**
     * Update a members login meta.
     *
     * @param integer $guid
     */
    private function updateMemberLogin($guid)
    {
        /** @var Request $request */
        $request = $this->app['request_stack']->getCurrentRequest();
        if ($this->records->getMember('guid', $guid)) {
            $this->records->updateMember($guid, [
                'lastseen' => date('Y-m-d H:i:s'),
                'lastip'   => $request->getClientIp(),
            ]);
        }
    }
}
