<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Stuur de gebruiker door naar Google OAuth.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->redirect();
    }

    /**
     * Verwerk de callback van Google.
     *
     * Werking:
     *
     * 1. Google-account ophalen.
     * 2. Eerst zoeken op google_id.
     * 3. Daarna zoeken op e-mailadres.
     * 4. Bestaand account aan Google koppelen.
     * 5. Of automatisch een nieuw Mashal-account aanmaken.
     * 6. Gebruiker direct inloggen.
     */
    public function callback(): RedirectResponse
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Google-gebruiker ophalen
            |--------------------------------------------------------------------------
            */

            $googleUser = Socialite::driver('google')->user();


            /*
            |--------------------------------------------------------------------------
            | Google-gegevens ophalen
            |--------------------------------------------------------------------------
            */

            $googleId = $googleUser->getId();

            $email = $googleUser->getEmail();

            $name = $googleUser->getName();

            $avatar = $googleUser->getAvatar();


            /*
            |--------------------------------------------------------------------------
            | Google ID controleren
            |--------------------------------------------------------------------------
            */

            if (!$googleId) {
                return redirect()
                    ->route('login')
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

            if (!$email) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Google heeft geen geldig e-mailadres teruggegeven.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | E-mailadres normaliseren
            |--------------------------------------------------------------------------
            */

            $email = strtolower(
                trim($email)
            );


            /*
            |--------------------------------------------------------------------------
            | Eerst zoeken op Google ID
            |--------------------------------------------------------------------------
            |
            | Dit is de meest betrouwbare manier om een reeds gekoppeld
            | Google-account terug te vinden.
            |
            */

            $user = User::where(
                'google_id',
                $googleId
            )->first();


            /*
            |--------------------------------------------------------------------------
            | Daarna zoeken op e-mailadres
            |--------------------------------------------------------------------------
            |
            | Als het Google-account nog niet gekoppeld is, maar er al wel
            | een normaal Mashal-account bestaat met hetzelfde e-mailadres,
            | koppelen we Google aan dat bestaande account.
            |
            | Hierdoor ontstaan geen dubbele accounts.
            |
            */

            if (!$user) {
                $user = User::where(
                    'email',
                    $email
                )->first();
            }


            /*
            |--------------------------------------------------------------------------
            | Nieuw Mashal-account aanmaken
            |--------------------------------------------------------------------------
            */

            if (!$user) {

                $user = User::create([

                    /*
                    |--------------------------------------------------------------
                    | Naam
                    |--------------------------------------------------------------
                    */

                    'name' => $name ?: 'Mashal gebruiker',


                    /*
                    |--------------------------------------------------------------
                    | E-mailadres
                    |--------------------------------------------------------------
                    */

                    'email' => $email,


                    /*
                    |--------------------------------------------------------------
                    | Google ID
                    |--------------------------------------------------------------
                    */

                    'google_id' => $googleId,


                    /*
                    |--------------------------------------------------------------
                    | Google profielfoto
                    |--------------------------------------------------------------
                    */

                    'google_avatar' => $avatar,


                    /*
                    |--------------------------------------------------------------
                    | Intern wachtwoord
                    |--------------------------------------------------------------
                    |
                    | Een Google-gebruiker hoeft dit wachtwoord niet te kennen.
                    |
                    | We genereren een sterk willekeurig wachtwoord zodat er
                    | nooit een voorspelbaar of leeg wachtwoord wordt opgeslagen.
                    |
                    */

                    'password' => Hash::make(
                        Str::random(64)
                    ),


                    /*
                    |--------------------------------------------------------------
                    | E-mail geverifieerd
                    |--------------------------------------------------------------
                    |
                    | Google heeft bevestigd dat de gebruiker toegang heeft
                    | tot dit Google-account.
                    |
                    */

                    'email_verified_at' => now(),


                    /*
                    |--------------------------------------------------------------
                    | Administrator
                    |--------------------------------------------------------------
                    |
                    | Een gebruiker die zichzelf met Google registreert krijgt
                    | nooit automatisch administratorrechten.
                    |
                    */

                    'is_admin' => false,

                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | Bestaand account koppelen / synchroniseren
                |--------------------------------------------------------------------------
                */

                $changed = false;


                /*
                |--------------------------------------------------------------
                | Google ID koppelen
                |--------------------------------------------------------------
                */

                if ($user->google_id !== $googleId) {

                    $user->google_id = $googleId;

                    $changed = true;

                }


                /*
                |--------------------------------------------------------------
                | Google avatar bijwerken
                |--------------------------------------------------------------
                */

                if (
                    $avatar &&
                    $user->google_avatar !== $avatar
                ) {

                    $user->google_avatar = $avatar;

                    $changed = true;

                }


                /*
                |--------------------------------------------------------------
                | E-mailverificatie
                |--------------------------------------------------------------
                */

                if (!$user->email_verified_at) {

                    $user->email_verified_at = now();

                    $changed = true;

                }


                /*
                |--------------------------------------------------------------
                | Alleen opslaan wanneer iets is gewijzigd
                |--------------------------------------------------------------
                */

                if ($changed) {
                    $user->save();
                }

            }


            /*
            |--------------------------------------------------------------------------
            | Gebruiker inloggen
            |--------------------------------------------------------------------------
            |
            | true betekent dat Laravel een remember-login mag gebruiken.
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
            | Na authenticatie regenereren we de session ID om bescherming
            | tegen session fixation te bieden.
            |
            */

            request()
                ->session()
                ->regenerate();


            /*
            |--------------------------------------------------------------------------
            | Redirect naar account
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Welkom bij Mashal Automotive. Je bent succesvol ingelogd met Google.'
                );


        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Fout intern loggen
            |--------------------------------------------------------------------------
            |
            | De technische fout komt in de Laravel logs terecht.
            | De bezoeker krijgt geen technische details te zien.
            |
            */

            report($exception);


            /*
            |--------------------------------------------------------------------------
            | Gebruiker terugsturen
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Inloggen met Google is niet gelukt. Probeer het opnieuw.'
                );

        }
    }
}