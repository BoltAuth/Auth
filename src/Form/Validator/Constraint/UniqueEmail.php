<?php

namespace Bolt\Extension\Bolt\Members\Form\Validator\Constraint;

use Bolt\Extension\Bolt\Members\Storage\Records;
use Symfony\Component\Validator\Constraint;

/**
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class UniqueEmail extends Constraint
{
    public $message = 'The provided email: "%string%" already exists, please choose a different one.';

    /** @var Records */
    private $records;

    /**
     * Constructor.
     *
     * @param Records $records
     * @param mixed   $options
     */
    public function __construct(Records $records, $options = null)
    {
        $this->records = $records;
        parent::__construct($options);
    }

    /**
     * @return Records
     */
    public function getRecords()
    {
        return $this->records;
    }
}
