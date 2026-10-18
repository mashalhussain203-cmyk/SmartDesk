<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        /*
         * Contactformulier: voorkom spam, maar geef bezoekers achter een
         * gedeeld IP-adres (mobiel netwerk, kantoor, Railway proxy) hun
         * eigen limiet. Er wordt uitsluitend POST /contact beperkt.
         *
         * De limiet per IP is ruim genoeg voor gedeelde netwerken. De
         * sessie- en e-maillimieten beperken herhaalde verzendingen.
         */
        /*
         * Video uploads were previously throttled at 3 requests per hour,
         * which also counted unsuccessful uploads. Give each visitor a new,
         * session-scoped limiter so existing hour-long lockouts stop immediately.
         * An IP cap still prevents abuse through repeated fresh sessions.
         */
        RateLimiter::for('video-optimizer-upload-v2', static function (Request $request): array {
            $visitor = $request->user()
                ? 'user:'.$request->user()->getAuthIdentifier()
                : 'session:'.$request->session()->getId();

            $response = static fn (Request $request, array $headers) => response()->json([
                'message' => __('Te veel video-uploads achter elkaar. Wacht even en probeer het opnieuw.'),
                'retry_after' => (int) ($headers['Retry-After'] ?? 60),
            ], 429, $headers);

            $limits = [
                Limit::perMinute(12)->by('video-upload-v2-minute:'.$visitor)->response($response),
                Limit::perHour(48)->by('video-upload-v2-hour:'.$visitor)->response($response),
                Limit::perHour(180)->by('video-upload-v2-ip:'.$request->ip())->response($response),
            ];

            if ($request->input('mode') === '4k120') {
                $limits[] = Limit::perHour(4)
                    ->by('video-4k120-hour:'.$visitor)
                    ->response($response);
            }

            return $limits;
        });

        /*
         * TikTok Upload Studio has its own per-user AND per-action limits.
         * Laravel's bare numeric throttles can share cache buckets across
         * unrelated routes, which caused POST /connect to return 429 even
         * when the user had not connected TikTok yet.
         */
        RateLimiter::for('tiktok-studio', static function (Request $request): Limit {
            $routeName = (string) ($request->route()?->getName() ?? 'unknown');
            $user = $request->user();
            $identity = $user
                ? 'user:'.$user->getAuthIdentifier()
                : 'session:'.$request->session()->getId();

            $limit = match ($routeName) {
                'tiktok-studio.connect' => Limit::perMinute(8),
                'tiktok-studio.callback' => Limit::perMinute(15),
                'tiktok-studio.disconnect' => Limit::perMinute(5),
                'tiktok-studio.upload' => Limit::perHour(5),
                'tiktok-studio.status' => Limit::perMinute(20),
                default => Limit::perMinute(5),
            };

            return $limit->by('tiktok-studio:'.$routeName.':'.$identity);
        });

        RateLimiter::for('contact-form', static function (Request $request): array {
            $email = mb_strtolower(trim((string) $request->input('email', '')));

            $limits = [
                Limit::perMinute(30)
                    ->by('contact-ip:'.$request->ip()),

                Limit::perMinutes(10, 5)
                    ->by('contact-session:'.$request->session()->getId()),
            ];

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $limits[] = Limit::perHour(5)
                    ->by('contact-email:'.hash('sha256', $email));
            }

            // A friendly redirect keeps the GET /contact page accessible
            // even when a visitor has sent too many form requests.
            return array_map(
                static fn (Limit $limit): Limit => $limit->response(
                    static fn (Request $request, array $headers) => redirect()
                        ->route('contact')
                        ->with('error', __('Te veel contactverzoeken. Wacht enkele minuten en probeer het opnieuw.'))
                        ->withInput($request->only(['first_name', 'last_name', 'email', 'message']))
                ),
                $limits
            );
        });
    }
}
