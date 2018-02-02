<?php
namespace Usedesk\SyncEngineIntegration;

use Illuminate\Support\ServiceProvider;

class SyncEngineIntegrationServiceProvider extends ServiceProvider {

	const CONFIG_PATH = __DIR__ . '/src/config/Config.php';
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('usedesk/sync-engine-integration');
		$this->loadRoutesFrom(__DIR__.'/routes/web.php');
		$this->loadMigrationsFrom(__DIR__.'/migrations');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
