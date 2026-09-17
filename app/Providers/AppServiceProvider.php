<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\TikTok\TikTokExtendSocialite;

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
        | SocialiteProviders registreren
        |--------------------------------------------------------------------------
        |
        | Laravel Socialite ondersteunt standaard onder andere Google,
        | GitHub en Facebook.
        |
        | TikTok wordt via SocialiteProviders toegevoegd.
        |
        | Wanneer SocialiteWasCalled wordt uitgevoerd, registreert
        | TikTokExtendSocialite de "tiktok" driver binnen Socialite.
        |
        | Hierdoor kunnen we later gebruiken:
        |
        | Socialite::driver('tiktok')
        |
        */

        Event::listen(
            SocialiteWasCalled::class,
            TikTokExtendSocialite::class
        );


        /*
        |--------------------------------------------------------------------------
        | HTTPS forceren in productie
        |--------------------------------------------------------------------------
        |
        | Railway draait achter een proxy/load balancer. Hierdoor kan Laravel
        | soms denken dat een request via HTTP binnenkomt terwijl de bezoeker
        | HTTPS gebruikt.
        |
        | In productie forceren we daarom HTTPS voor alle gegenereerde URLs,
        | routes, formulieren, redirects en links.
        |
        */

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}