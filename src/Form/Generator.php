<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\FormBuilderEvent;
use Bolt\Extension\Bolt\Members\Form\Builder;
use Bolt\Extension\Bolt\Members\Storage\Records;
use Pimple as Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Form object generator.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Generator
{
    /** @var Config */
    private $config;
    /** @var Records */
    private $records;
    /** @var Container */
    private $formComponents;
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var Session */
    private $session;
    /** @var EventDispatcherInterface $dispatcher */
    private $dispatcher;
    /** @var array */
    private $formMap = [
        'associate'                => [
            'form' => Builder\Associate::class,
            'type' => Type\AssociateType::class,
        ],
        'login_oauth'              => [
            'form' => Builder\LoginOauth::class,
            'type' => Type\LoginOauthType::class,
        ],
        'login_password'           => [
            'form' => Builder\LoginPassword::class,
            'type' => Type\LoginPasswordType::class,
        ],
        'logout'                   => [
            'form' => Builder\Logout::class,
            'type' => Type\LogoutType::class,
        ],
        'profile_edit'             => [
            'form' => Builder\Profile::class,
            'type' => Type\ProfileEditType::class,
        ],
        'profile_recovery_request' => [
            'form' => Builder\ProfileRecovery::class,
            'type' => Type\ProfileRecoveryRequestType::class,
        ],
        'profile_recovery_submit'  => [
            'form' => Builder\ProfileRecovery::class,
            'type' => Type\ProfileRecoverySubmitType::class,
        ],
        'profile_register'         => [
            'form' => Builder\ProfileRegister::class,
            'type' => Type\ProfileRegisterType::class,
        ],
        'profile_view'             => [
            'form' => Builder\Profile::class,
            'type' => Type\ProfileViewType::class,
        ],
    ];

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param Records                  $records
     * @param Container                $formComponents
     * @param FormFactoryInterface     $formFactory
     * @param Session                  $session
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Config $config,
        Records $records,
        Container $formComponents,
        FormFactoryInterface $formFactory,
        Session $session,
        EventDispatcherInterface $dispatcher
    ) {
        $this->config = $config;
        $this->records = $records;
        $this->formComponents = $formComponents;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Build a form object.
     *
     * @param string $formName
     *
     * @return Builder\MembersFormBuilderInterface
     */
    public function getResolvedBuild($formName)
    {
        if (!isset($this->formMap[$formName])) {
            throw new \RuntimeException(sprintf('Invalid type request for non-existing form: %s', $formName));
        }

        $event = new FormBuilderEvent($formName);
        $this->dispatcher->dispatch(FormBuilderEvent::BUILD, $event);

        $class = $this->formMap[$formName]['form'];
        $type = $event->getType() ?: $this->getType($formName);
        $entity = $event->getEntity() ?: $this->formComponents['entity']['profile'];

        return new $class($this->formFactory, $type, $entity);
    }

    /**
     * Return the registered type object for the form.
     *
     * @param string $formName
     *
     * @return FormTypeInterface
     */
    private function getType($formName)
    {
        $typeName = $this->formMap[$formName]['type'];
        /** @var FormTypeInterface $class */
        $class = new $typeName($this->config);

        if ($class instanceof Builder\SessionAwareInterface) {
            $class->setSession($this->session);
        }
        if ($class instanceof Builder\StorageAwareInterface) {
            $class->setRecords($this->records);
        }

        return $class;
    }
}
