<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuthSuccessAnimationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            Login::class,
            static function (Login $event): void {
                $request = request();

                if (! $request->hasSession()) {
                    return;
                }

                /*
                 * De normale /verify-pagina toont zelf al exact dezelfde
                 * groene succesanimatie voordat die doorstuurt. Daar willen
                 * we geen tweede dubbele animatie bovenop zetten.
                 */
                if ($request->routeIs('verification.verify')) {
                    return;
                }

                $request->session()->flash(
                    'auth_success_animation',
                    [
                        'user_id' =>
                            (int) $event->user->getAuthIdentifier(),

                        'provider' =>
                            method_exists(
                                $event->user,
                                'loginProvider'
                            )
                                ? (string) $event->user->loginProvider()
                                : null,
                    ]
                );
            }
        );
    }
}
