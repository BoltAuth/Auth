<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Config\Config;
use Bolt\Extension\Bolt\Members\Event\FormBuilderEvent;
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
    private $formMap;

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
        $event = $this->getEvent($formName);
        $formMap = $this->getFormMap($formName);
        $builderClassName = $formMap['form'];

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
        $formMap = $this->getFormMap($formName);
        $typeName = $formMap['type'];

        /** @var FormTypeInterface $typeClass */
        $typeClass = new $typeName($this->config);

        return $typeClass;
    }

    /**
     * Return the registered entity object for the form.
     *
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
        if ($event->getEntityClass() !== null) {
            $data = $entity ? $entity->toArray() : [];
            $className = $event->getEntityClass();

            return new $className((array) $data);
        }
        // If no event override, but we've been passed an Entity object, return it
        if ($entity !== null) {
            return $entity;
        }

        return new Profile();
    }

    /**
     * Return the form definition hash map.
     *
     * @param string $formName
     *
     * @return array
     */
    private function getFormMap($formName)
    {
        if ($this->formMap === null) {
            $this->formMap = [
                MembersForms::FORM_ASSOCIATE                => [
                    'form' => Builder\Associate::class,
                    'type' => Type\AssociateType::class,
                ],
                MembersForms::FORM_LOGIN_OAUTH              => [
                    'form' => Builder\LoginOauth::class,
                    'type' => Type\LoginOauthType::class,
                ],
                MembersForms::FORM_LOGIN_PASSWORD           => [
                    'form' => Builder\LoginPassword::class,
                    'type' => Type\LoginPasswordType::class,
                ],
                MembersForms::FORM_LOGOUT                   => [
                    'form' => Builder\Logout::class,
                    'type' => Type\LogoutType::class,
                ],
                MembersForms::FORM_PROFILE_EDIT             => [
                    'form' => Builder\Profile::class,
                    'type' => Type\ProfileEditType::class,
                ],
                MembersForms::FORM_PROFILE_RECOVER_REQUEST => [
                    'form' => Builder\ProfileRecovery::class,
                    'type' => Type\ProfileRecoveryRequestType::class,
                ],
                MembersForms::FORM_PROFILE_RECOVER_SUBMIT  => [
                    'form' => Builder\ProfileRecovery::class,
                    'type' => Type\ProfileRecoverySubmitType::class,
                ],
                MembersForms::FORM_PROFILE_REGISTER         => [
                    'form' => Builder\ProfileRegister::class,
                    'type' => Type\ProfileRegisterType::class,
                ],
                MembersForms::FORM_PROFILE_VIEW             => [
                    'form' => Builder\Profile::class,
                    'type' => Type\ProfileViewType::class,
                ],
            ];
        }

        if (!isset($this->formMap[$formName])) {
            throw new \RuntimeException(sprintf('Invalid builder request for non-existing form name: %s', $formName));
        }

        return $this->formMap[$formName];
    }
}
