<?php

namespace Bolt\Extension\Bolt\Members\Form\Builder;

use Bolt\Extension\Bolt\Members\AccessControl\Session;

/**
 * Interface for classes needing access control session.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
interface SessionAwareInterface
{
    /**
     * @param Session $session
     */
    public function setSession(Session $session);
}
