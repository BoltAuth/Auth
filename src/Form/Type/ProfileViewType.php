<?php

namespace Bolt\Extension\Bolt\Members\Form\Type;

/**
 * Profile view type.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ProfileViewType extends ProfileEditType
{
    /** @var boolean */
    protected $requirePassword = false;
}
