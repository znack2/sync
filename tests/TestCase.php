<?php

namespace Usedesk\SyncIntegration\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
// use Orchestra\Testbench\TestCase;

use Usedesk\SyncIntegration\SyncServiceProvider;
use Usedesk\SyncIntegration\ServiceProvider;

abstract class TestCase extends AbstractPackageTestCase
{
    /**
     * Get the service provider class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return string
     */
    // protected function getServiceProviderClass($app)
    // {
    //     return SyncServiceProvider::class;
    // }

    // protected function getPackageProviders($app)
    // {
    //     return [ServiceProvider::class];
    // }

    // protected function getPackageAliases($app)
    // {
    //     return [
    //         'sync_integration' => Sync::class,
    //     ];
    // }
}
