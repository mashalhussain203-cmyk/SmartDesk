<?php

namespace App\Providers;

use App\Models\User;
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
                 * De normale verificatiepagina toont zelf al
                 * de groene succesanimatie.
                 */
                if ($request->routeIs('verification.verify')) {
                    return;
                }

                $provider = null;

                if ($event->user instanceof User) {
                    $provider =
                        $event->user->loginProvider();
                }

                $request->session()->flash(
                    'auth_success_animation',
                    [
                        'user_id' =>
                            (int) $event->user->getAuthIdentifier(),

                        'provider' =>
                            $provider,
                    ]
                );
            }
        );
    }
}