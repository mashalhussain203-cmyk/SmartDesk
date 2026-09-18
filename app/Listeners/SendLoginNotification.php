<?php

namespace App\Listeners;

use App\Services\LoginSecurityService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendLoginNotification
{
    public function __construct(
        private readonly LoginSecurityService $loginSecurity
    ) {
    }

    /**
     * Verwerk iedere succesvolle Laravel-login.
     *
     * Laravel ontdekt listeners in app/Listeners automatisch wanneer
     * de handle()-methode het event type-hint.
     */
    public function handle(Login $event): void
    {
        try {
            $this->loginSecurity->recordLogin(
                $event,
                request()
            );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Beveiligingslogging mag de login zelf niet breken
            |--------------------------------------------------------------------------
            */

            Log::error(
                'LoginSecurity listener kon de login niet verwerken.',
                [
                    'user_id' => method_exists(
                        $event->user,
                        'getAuthIdentifier'
                    )
                        ? $event->user->getAuthIdentifier()
                        : null,
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }
}
