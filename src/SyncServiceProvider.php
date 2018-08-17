<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration;

use Illuminate\Support\ServiceProvider;

class SyncServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/sync_integration.php';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('sync_integration.php'),
        ], 'config');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'sync_integration'
        );

        $this->registerCommand();
    }
    protected function registerCommand()
    {

    }
}