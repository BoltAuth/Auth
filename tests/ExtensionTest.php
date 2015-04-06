<?php

namespace Bolt\Extension\Bolt\Members\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\Bolt\Members\Extension;

/**
 * Ensure that Members loads correctly.
 *
 */
class ExtensionTest extends BoltUnitTest
{
    public function testExtensionRegister()
    {
        $app = $this->getApp();
        $extension = new Extension($app);
        $app['extensions']->register( $extension );
        $name = $extension->getName();
        $this->assertSame($name, 'Members');
        $this->assertSame($extension, $app["extensions.$name"]);
    }
}
