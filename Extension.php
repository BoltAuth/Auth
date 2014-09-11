<?php

namespace Bolt\Extension\Bolt\Membership;

/**
 *
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Extension extends \Bolt\BaseExtension
{
    const NAME = 'Membership';

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize()
    {
        /*
         * Backend
         */
        if ($this->app['config']->getWhichEnd() == 'backend') {
            // Check & create database tables if required
            $records = new MembershipRecords($this->app);
            $records->dbCheck();
        }

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() == 'frontend') {
        }
    }
}
