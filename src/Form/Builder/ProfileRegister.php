<?php

namespace Bolt\Extension\Bolt\Members\Form\Builder;

use Bolt\Extension\Bolt\Members\Form\Entity;
use Bolt\Extension\Bolt\Members\Form\Type;

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
}
