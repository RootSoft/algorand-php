<?php

namespace Rootsoft\Algorand\Tests;

use Illuminate\Foundation\Application;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * If you need to add something early in the application bootstrapping process
     * (which executed between registering service providers and booting service providers).
     * @param Application $app
     */
    protected function getEnvironmentSetUp(Application $app)
    {
    }
}
