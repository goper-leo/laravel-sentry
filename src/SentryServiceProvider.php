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
            return $this->app->make('EETechMedia\Sentry\sentry');
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
    }
}
