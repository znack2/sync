<?php

namespace usedesk\SyncEngineIntegration\Facades;

use Illuminate\Support\Facades\Facade;

class SyncEngineIntegration extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sync-engine-integration';
    }
}
