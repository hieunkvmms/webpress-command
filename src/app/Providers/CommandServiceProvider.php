<?php

namespace Hieunk\Command\Providers;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'webpress.command');

        $this->publishes([
            __DIR__ . '/../../config/webpress-component.php' => config_path('webpress-component.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Hieunk\Command\Console\Commands\CreateComponentCommand::class,
            ]);
        }
    }
}
