<?php
namespace EETechMedia\Sentry;

use Illuminate\Support\ServiceProvider;

class SentryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sentry', function () {
            return $this->app->make('EETechMedia\Sentry\Sentry');
        });
    }

    /**
     * Boot
     *
     * @return void
     */
    public function boot()
    {
        // Add resources here...
        $this->publishes([
            __DIR__.'/config/sentry.php' => config_path('sentry.php'),
            __DIR__.'/config/geoip.php' => config_path('geoip.php')
        ]);

        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }
}
