<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Extension\Bolt\Members\Validator\Constraints\ValidUsername;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',    'text',   array(
                'label'       => __('User name:'),
                'data'        => $options['data']['username'],
                'constraints' => array(
                    new ValidUsername(),
                    new Assert\NotBlank()
                )))
            ->add('displayname', 'text',   array(
                'label'       => __('Publicly visible name:'),
                'data'        => $options['data']['displayname'],
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 2))
                )))
            ->add('email',       'text',   array(
                'label'       => __('Email:'),
                'data'        => $options['data']['email'],
                'constraints' => new Assert\Email(array(
                    'message' => 'The address "{{ value }}" is not a valid email.',
                    'checkMX' => true)
                )))
            ->add('submit',      'submit', array(
                'label'       => __('Save & continue')
                ));
    }

    public function getName()
    {
        return 'register';
    }

}
