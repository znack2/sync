<?php declare(strict_types=1);

namespace Freshplan\Sync\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
// use Orchestra\Testbench\TestCase;

use Freshplan\Sync\SyncServiceProvider;
use Freshplan\Sync\ServiceProvider;

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
