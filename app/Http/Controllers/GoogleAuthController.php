<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Google OAuth redirect
    |--------------------------------------------------------------------------
    */

    public function redirect(): RedirectResponse
    {
        return Socialite::driver(
            'google'
        )->redirect();
    }


    /*
    |--------------------------------------------------------------------------
    | Google OAuth callback
    |--------------------------------------------------------------------------
    |
    | Werking:
    |
    | 1. Google-profiel ophalen.
    | 2. Google-ID en e-mailadres controleren.
    | 3. Eerst zoeken op google_id.
    | 4. Daarna zoeken op e-mailadres.
    | 5. Bestaand account koppelen/synchroniseren of nieuw account maken.
    | 6. login_provider vóór Auth::login() op de request zetten.
    | 7. Auth::login() vuurt Laravel's Login-event af.
    | 8. LoginSecurityService leest de browsercontext die vóór de OAuth-
    |    redirect via /login-security/context in dezelfde sessie is opgeslagen.
    | 9. Na de login wordt de sessie-ID vernieuwd.
    |
    */

    public function callback(
        Request $request
    ): RedirectResponse {
        try {
            /*
            |--------------------------------------------------------------------------
            | Google-gebruiker ophalen
            |--------------------------------------------------------------------------
            */

            $googleUser = Socialite::driver(
                'google'
            )->user();


            /*
            |--------------------------------------------------------------------------
            | Google-gegevens normaliseren
            |--------------------------------------------------------------------------
            */

            $googleId = trim(
                (string) $googleUser->getId()
            );

            $email = strtolower(
                trim(
                    (string) $googleUser->getEmail()
                )
            );

            $name = trim(
                (string) $googleUser->getName()
            );

            $avatar = trim(
                (string) $googleUser->getAvatar()
            );


            /*
            |--------------------------------------------------------------------------
            | Google-ID controleren
            |--------------------------------------------------------------------------
            */

            if ($googleId === '') {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route(
                        'login'
                    )
                    ->with(
                        'error',
                        'Google heeft geen geldige account-ID teruggegeven.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | E-mailadres controleren
            |--------------------------------------------------------------------------
            */

            if ($email === '') {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route(
                        'login'
                    )
                    ->with(
                        'error',
                        'Google heeft geen geldig e-mailadres teruggegeven.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Eerst zoeken op Google-ID
            |--------------------------------------------------------------------------
            |
            | Dit is de primaire koppeling voor een reeds verbonden
            | Google-account.
            |
            */

            $user = User::query()
                ->where(
                    'google_id',
                    $googleId
                )
                ->first();


            /*
            |--------------------------------------------------------------------------
            | Daarna zoeken op e-mailadres
            |--------------------------------------------------------------------------
            |
            | Hierdoor koppelen we een bestaand Mashal-account met hetzelfde
            | e-mailadres aan Google in plaats van een dubbel account te maken.
            |
            */

            if (! $user) {
                $user = User::query()
                    ->where(
                        'email',
                        $email
                    )
                    ->first();
            }


            /*
            |--------------------------------------------------------------------------
            | Nieuw Mashal-account
            |--------------------------------------------------------------------------
            */

            if (! $user) {
                $user = User::create([
                    'name' =>
                        $name !== ''
                            ? $name
                            : $this->displayNameFromEmail(
                                $email
                            ),

                    'email' =>
                        $email,

                    'google_id' =>
                        $googleId,

                    'google_avatar' =>
                        $avatar !== ''
                            ? $avatar
                            : null,

                    'login_provider' =>
                        'google',

                    /*
                    |--------------------------------------------------------------------------
                    | Intern willekeurig wachtwoord
                    |--------------------------------------------------------------------------
                    |
                    | Een OAuth-gebruiker hoeft dit wachtwoord niet te kennen.
                    |
                    */

                    'password' =>
                        Hash::make(
                            Str::random(
                                64
                            )
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | Google-login bevestigt toegang tot het Google-account
                    |--------------------------------------------------------------------------
                    */

                    'email_verified_at' =>
                        now(),

                    /*
                    |--------------------------------------------------------------------------
                    | Zelfregistratie geeft nooit adminrechten
                    |--------------------------------------------------------------------------
                    */

                    'is_admin' =>
                        false,
                ]);
            } else {
                /*
                |--------------------------------------------------------------------------
                | Bestaand account synchroniseren
                |--------------------------------------------------------------------------
                */

                $changed = false;


                /*
                |--------------------------------------------------------------------------
                | Google-ID koppelen
                |--------------------------------------------------------------------------
                */

                if (
                    (string) $user->google_id !==
                    $googleId
                ) {
                    $user->google_id =
                        $googleId;

                    $changed =
                        true;
                }


                /*
                |--------------------------------------------------------------------------
                | Google-avatar bijwerken
                |--------------------------------------------------------------------------
                */

                if (
                    $avatar !== '' &&
                    (string) $user->google_avatar !==
                    $avatar
                ) {
                    $user->google_avatar =
                        $avatar;

                    $changed =
                        true;
                }


                /*
                |--------------------------------------------------------------------------
                | E-mail als geverifieerd markeren
                |--------------------------------------------------------------------------
                */

                if (! $user->email_verified_at) {
                    $user->email_verified_at =
                        now();

                    $changed =
                        true;
                }


                /*
                |--------------------------------------------------------------------------
                | Laatste loginmethode
                |--------------------------------------------------------------------------
                */

                if (
                    (string) $user->login_provider !==
                    'google'
                ) {
                    $user->login_provider =
                        'google';

                    $changed =
                        true;
                }


                /*
                |--------------------------------------------------------------------------
                | Alleen opslaan bij wijzigingen
                |--------------------------------------------------------------------------
                */

                if ($changed) {
                    $user->save();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Login-provider vóór Auth::login beschikbaar maken
            |--------------------------------------------------------------------------
            |
            | Laravel vuurt het Login-event tijdens Auth::login() af.
            | De security-listener kan hierdoor direct zien dat deze login
            | via Google plaatsvindt.
            |
            */

            $request->merge([
                'login_provider' =>
                    'google',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Gebruiker inloggen
            |--------------------------------------------------------------------------
            |
            | true behoudt het bestaande gedrag: Laravel mag een remember-login
            | gebruiken.
            |
            */

            Auth::login(
                $user,
                true
            );


            /*
            |--------------------------------------------------------------------------
            | Sessiebeveiliging
            |--------------------------------------------------------------------------
            |
            | De Login-listener is op dit moment al uitgevoerd. Daarna vernieuwen
            | we de session ID tegen session fixation.
            |
            */

            $request
                ->session()
                ->regenerate();


            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'account'
                )
                ->with(
                    'success',
                    'Welkom bij Mashal Automotive. Je bent succesvol ingelogd met Google.'
                );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Technische fout intern loggen
            |--------------------------------------------------------------------------
            */

            report(
                $exception
            );


            /*
            |--------------------------------------------------------------------------
            | Eventuele oude browser-securitycontext opruimen
            |--------------------------------------------------------------------------
            |
            | Bij een mislukte OAuth-callback heeft geen succesvolle login
            | plaatsgevonden. We voorkomen daarom dat deze context later per
            | ongeluk bij een andere login wordt gebruikt.
            |
            */

            $this->forgetLoginSecurityBrowserContext(
                $request
            );


            /*
            |--------------------------------------------------------------------------
            | Veilige foutmelding voor gebruiker
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Inloggen met Google is niet gelukt. Probeer het opnieuw.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Leesbare naam uit e-mailadres
    |--------------------------------------------------------------------------
    */

    private function displayNameFromEmail(
        string $email
    ): string {
        $emailName = trim(
            Str::before(
                $email,
                '@'
            )
        );

        $displayName = Str::of(
            $emailName
        )
            ->replace(
                [
                    '.',
                    '_',
                    '-',
                ],
                ' '
            )
            ->squish()
            ->title()
            ->toString();

        return $displayName !== ''
            ? $displayName
            : 'Mashal gebruiker';
    }


    /*
    |--------------------------------------------------------------------------
    | Tijdelijke login-security browsercontext opruimen
    |--------------------------------------------------------------------------
    |
    | Normaal wordt deze context na een succesvolle login door de
    | LoginSecurityService verwijderd. Deze helper is alleen voor OAuth-
    | fouten vóórdat Auth::login() succesvol is uitgevoerd.
    |
    */

    private function forgetLoginSecurityBrowserContext(
        Request $request
    ): void {
        if (! $request->hasSession()) {
            return;
        }

        $request->session()->forget([
            'login_security.browser_timezone',
            'login_security.latitude',
            'login_security.longitude',
            'login_security.location_accuracy',
            'login_security.location_permission',
            'login_security.context_captured_at',
        ]);
    }
}
