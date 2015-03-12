<?php

namespace Bolt\Extension\Bolt\Members\Form;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Profile type
 *
 * Copyright (C) 2014  Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',    'text',   array(
                'label'       => Trans::__('User name:'),
                'data'        => $options['data']['username'],
                'read_only'   => true,
                'constraints' => array(
                )))
            ->add('displayname', 'text',   array(
                'label'       => Trans::__('Publicly visible name:'),
                'data'        => $options['data']['displayname'],
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 2))
                )))
            ->add('email',       'text',   array(
                'label'       => Trans::__('Email:'),
                'data'        => $options['data']['email'],
                'constraints' => new Assert\Email(array(
                    'message' => 'The address "{{ value }}" is not a valid email.',
                    'checkMX' => true)
                )))
            ->add('submit',      'submit', array(
                    'label'   => Trans::__('Save & continue')
                ));
    }

    public function getName()
    {
        return 'profile';
    }
}
