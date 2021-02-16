<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Builder;

use Bolt\Extension\BoltAuth\Auth\Form\Entity;
use Bolt\Extension\BoltAuth\Auth\Form\Type;

/**
 * Password reset form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileRecovery extends AbstractFormBuilder
{
    /** @var Type\AbstractProfileRecoveryType */
    protected $type;
    /** @var Entity\Profile */
    protected $entity;
}
