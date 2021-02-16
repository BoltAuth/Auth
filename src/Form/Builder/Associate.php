<?php

namespace Bolt\Extension\BoltAuth\Auth\Form\Builder;

use Bolt\Extension\BoltAuth\Auth\Form\Entity;
use Bolt\Extension\BoltAuth\Auth\Form\Type;

/**
 * Social media account association form.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Associate extends AbstractFormBuilder
{
    /** @var Type\AssociateType */
    protected $type;
    /** @var Entity\Profile */
    protected $entity;
}
