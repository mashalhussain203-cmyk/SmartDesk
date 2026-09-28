<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\Provider as MicrosoftProvider;
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
        | Laravel Socialite ondersteunt standaard verschillende OAuth-providers.
        | Extra providers worden via SocialiteProviders geregistreerd.
        |
        | In Mashal Studio gebruiken we hier:
        |
        | - TikTok
        | - Microsoft / Hotmail / Outlook / Live / Entra ID
        |
        */

        /*
        |--------------------------------------------------------------------------
        | TikTok Socialite-provider
        |--------------------------------------------------------------------------
        |
        | Hierdoor kan de applicatie gebruiken:
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
        | Microsoft Socialite-provider
        |--------------------------------------------------------------------------
        |
        | De Microsoft-provider komt uit:
        |
        | socialiteproviders/microsoft
        |
        | Hierdoor kan de applicatie gebruiken:
        |
        | Socialite::driver('microsoft')
        |
        | De client ID, client secret, redirect URI en tenant worden geladen uit
        | config/services.php en Railway/.env.
        |
        */

        Event::listen(
            SocialiteWasCalled::class,
            function (SocialiteWasCalled $event): void {
                $event->extendSocialite(
                    'microsoft',
                    MicrosoftProvider::class
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | HTTPS forceren in productie
        |--------------------------------------------------------------------------
        |
        | Railway draait achter een proxy/load balancer. Laravel kan daardoor
        | soms denken dat een request via HTTP binnenkomt terwijl de bezoeker
        | daadwerkelijk HTTPS gebruikt.
        |
        | In productie forceren we daarom HTTPS voor gegenereerde URLs,
        | routes, formulieren, redirects en OAuth callback-links.
        |
        */

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
