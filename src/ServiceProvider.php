<?php

namespace Usedesk\SyncEngineIntegration;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/sync-engine-integration.php';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('sync-engine-integration.php'),
        ], 'config');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations');

    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'sync-engine-integration'
        );

        $this->app->bind('sync-engine-integration', function () {
            return new SyncEngineIntegration();
        });
        $this->registerCommand();
    }
    protected function registerCommand()
    {

    }
}
