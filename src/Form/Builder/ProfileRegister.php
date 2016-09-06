<?php

namespace Bolt\Extension\Bolt\Members\Form\Builder;

use Bolt\Extension\Bolt\Members\AccessControl\Session;
use Bolt\Extension\Bolt\Members\Form\Entity;
use Bolt\Extension\Bolt\Members\Form\Type;
use Bolt\Extension\Bolt\Members\Storage;
use League\OAuth2\Client\Provider\AbstractProvider;

/**
 * Register form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileRegister extends AbstractFormBuilder
{
    /** @var Type\ProfileRegisterType */
    protected $type;
    /** @var Entity\Profile */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function getData(Storage\Records $records)
    {
        if ($this->session === null) {
            throw new \RuntimeException('Members session not set.');
        }

        if ($this->session->isTransitional()) {
            $resourceOwner = $this->session->getTransitionalProvider()->getResourceOwner();
            $this->entity->setDisplayname($resourceOwner->getName());
            $this->entity->setEmail($resourceOwner->getEmail());
        }

$data = parent::getData($records);

        return $data + [
            'data'            => $this->entity,
        ];
    }
}
