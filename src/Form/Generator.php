<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\FormBuilderEvent;
use Bolt\Extension\Bolt\Members\Form\Builder;
use Bolt\Extension\Bolt\Members\Form\Entity\EntityInterface;
use Bolt\Extension\Bolt\Members\Form\Entity\Profile;
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
    /** @var Container */
    private $formTypes;
    /** @var FormFactoryInterface */
    private $formFactory;
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
     * @param Container                $formTypes
     * @param FormFactoryInterface     $formFactory
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Config $config,
        Container $formTypes,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->config = $config;
        $this->formTypes = $formTypes;
        $this->formFactory = $formFactory;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Build a form object.
     *
     * @param string            $formName
     * @param FormTypeInterface $type
     * @param EntityInterface   $entity
     *
     * @return Builder\AbstractFormBuilder
     */
    public function getFormBuilder($formName, FormTypeInterface $type = null, EntityInterface $entity = null)
    {
        if (!isset($this->formMap[$formName])) {
            throw new \RuntimeException(sprintf('Invalid builder request for non-existing form name: %s', $formName));
        }

        $event = $this->getEvent($formName);
        $builderClassName = $this->formMap[$formName]['form'];

        $type = $this->getType($event, $type);
        $entity = $this->getEntity($event, $entity);

        $builder =  new $builderClassName($this->formFactory, $type, $entity);

        return $builder;
    }

    /**
     * Return a dispatched event object for type and data collection.
     *
     * @param string $formName
     *
     * @return FormBuilderEvent
     */
    private function getEvent($formName)
    {
        $event = new FormBuilderEvent($formName);
        $this->dispatcher->dispatch(FormBuilderEvent::BUILD, $event);

        return $event;
    }

    /**
     * Return the registered type object for the form.
     *
     * @param FormBuilderEvent       $event
     * @param FormTypeInterface|null $type
     *
     * @return FormTypeInterface
     */
    private function getType(FormBuilderEvent $event, $type)
    {
        // A 'type' object added in the event is considered an override
        if ($event->getType() !== null) {
            return $event->getType();
        }
        // If no event override, but we've been passed a Type object, return it
        if ($type !== null) {
            return $type;
        }

        $formName = $event->getName();
        $typeName = $this->formMap[$formName]['type'];
        /** @var FormTypeInterface $class */
        $class = new $typeName($this->config);

        return $class;
    }

    /**
     * @param FormBuilderEvent     $event
     * @param EntityInterface|null $entity
     *
     * @return EntityInterface
     */
    private function getEntity(FormBuilderEvent $event, $entity)
    {
        // An 'entity' object added in the event is considered an override
        if ($event->getEntity() !== null) {
            return $event->getEntity();
        }
        // If no event override, but we've been passed an Entity object, return it
        if ($entity !== null) {
            return $entity;
        }

        return new Profile();
    }
}
