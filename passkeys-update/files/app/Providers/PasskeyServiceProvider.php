<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class PasskeyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(Login::class, static function (Login $event): void {
            $request = request();
            /** Only explicit authentication endpoints count as recent login. */
            if ($request->hasSession() && ! Auth::guard($event->guard)->viaRemember() && $request->routeIs(
                'login.submit', 'email-login.verify', 'email-login.link.complete',
                'google.callback', 'github.callback', 'facebook.callback',
                'linkedin.callback', 'tiktok.callback', 'tiktok.complete.submit',
                'verification.verify', 'passkeys.login.verify'
            )) {
                $request->session()->put('passkeys.recent_login', [
                    'user_id' => (string) $event->user->getAuthIdentifier(),
                    'at' => time(),
                ]);
            }
        });
    }
}
