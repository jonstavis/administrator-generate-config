<?php namespace Yottaram\AdministratorConfig;

use Illuminate\Support\ServiceProvider;
use Yottaram\AdministratorConfig\Commands\AdministratorConfigCommand;

class AdministratorConfigServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app['administrator.config'] = $this->app->share(function($app) {
            return new AdministratorConfigCommand;
        });    
        $this->commands('administrator.config');
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
