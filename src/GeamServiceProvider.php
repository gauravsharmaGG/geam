<?php

namespace Goksgreat\Geam;

use Goksgreat\Geam\Creator\CommanderGenerate;

use Illuminate\Support\ServiceProvider;

class GeamServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/config.php', 'geam-creator'
        );

        $this->app->singleton('command.geam.generate', function ($app) {
            return new CommanderGenerate($app['files']);
        });

        $this->commands('command.geam.generate');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('geam-creator.php'),
        ]);

        require app_path(config('geam-creator.geam_route'));
    }
}
