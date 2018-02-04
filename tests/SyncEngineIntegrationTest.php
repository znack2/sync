<?php

namespace usedesk\SyncEngineIntegration\Tests;

use usedesk\SyncEngineIntegration\Facades\SyncEngineIntegration;
use usedesk\SyncEngineIntegration\ServiceProvider;
use Orchestra\Testbench\TestCase;

class SyncEngineIntegrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'sync-engine-integration' => SyncEngineIntegration::class,
        ];
    }

    public function testExample()
    {
        assertEquals(1, 1);
    }
}
