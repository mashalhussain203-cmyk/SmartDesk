<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | HTTPS forceren in productie
        |--------------------------------------------------------------------------
        |
        | Railway draait achter een proxy/load balancer. Hierdoor kan Laravel
        | soms denken dat een request via HTTP binnenkomt terwijl de bezoeker
        | HTTPS gebruikt. In productie forceren we daarom HTTPS voor alle
        | gegenereerde URLs, routes, formulieren en links.
        |
        */

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
