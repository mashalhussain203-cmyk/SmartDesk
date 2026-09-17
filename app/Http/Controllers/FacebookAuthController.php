<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\FacebookProvider;
use Throwable;

class FacebookAuthController extends Controller
{
    public function __construct(
        private readonly BrevoMailService $brevoMail
    ) {
    }

    /**
     * Stuur de gebruiker door naar Facebook OAuth.
     */
    public function redirect(): RedirectResponse
    {
        /** @var FacebookProvider $provider */
        $provider = Socialite::driver('facebook');

        return $provider
            ->scopes([
                'email',
                'public_profile',
            ])
            ->redirect();
    }

    /**
     * Verwerk de callback van Facebook.
     */
    public function callback(): RedirectResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Facebook provider
            |--------------------------------------------------------------------------
            */

            /** @var FacebookProvider $provider */
            $provider = Socialite::driver('facebook');

            $facebookUser = $provider->user();


            /*
            |--------------------------------------------------------------------------
            | Facebook gegevens ophalen
            |--------------------------------------------------------------------------
            */

            $facebookId = trim(
                (string) $facebookUser->getId()
            );

            $email = strtolower(
                trim(
                    (string) $facebookUser->getEmail()
                )
            );

            $name = trim(
                (string) $facebookUser->getName()
            );

            $nickname = trim(
                (string) $facebookUser->getNickname()
            );

            $avatar = $facebookUser->getAvatar();


            /*
            |--------------------------------------------------------------------------
            | Facebook ID controleren
            |--------------------------------------------------------------------------
            */

            if ($facebookId === '') {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Facebook kon je account niet correct identificeren. Probeer opnieuw in te loggen.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | E-mailadres controleren
            |--------------------------------------------------------------------------
            */

            if ($email === '') {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Facebook heeft geen e-mailadres beschikbaar gesteld. Geef Mashal Automotive toestemming om je e-mailadres te gebruiken en probeer opnieuw.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Naam bepalen
            |--------------------------------------------------------------------------
            */

            $displayName = $name !== ''
                ? $name
                : (
                    $nickname !== ''
                        ? $nickname
                        : 'Mashal gebruiker'
                );


            /*
            |--------------------------------------------------------------------------
            | Nieuwe gebruiker status
            |--------------------------------------------------------------------------
            */

            $isNewUser = false;


            /*
            |--------------------------------------------------------------------------
            | Eerst zoeken op Facebook ID
            |--------------------------------------------------------------------------
            */

            $user = User::where(
                'facebook_id',
                $facebookId
            )->first();


            /*
            |--------------------------------------------------------------------------
            | Daarna zoeken op e-mailadres
            |--------------------------------------------------------------------------
            */

            if (! $user) {
                $user = User::where(
                    'email',
                    $email
                )->first();
            }


            /*
            |--------------------------------------------------------------------------
            | Bestaande Facebook-koppeling controleren
            |--------------------------------------------------------------------------
            */

            if (
                $user &&
                filled($user->facebook_id) &&
                $user->facebook_id !== $facebookId
            ) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Dit Mashal-account is al gekoppeld aan een ander Facebook-account.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Nieuwe gebruiker aanmaken
            |--------------------------------------------------------------------------
            */

            if (! $user) {
                $user = User::create([
                    'name' => $displayName,

                    'email' => $email,

                    'facebook_id' => $facebookId,

                    'facebook_avatar' => $avatar,

                    'password' => Hash::make(
                        Str::random(64)
                    ),

                    'email_verified_at' => now(),

                    'is_admin' => false,
                ]);

                $isNewUser = true;
            } else {

                /*
                |--------------------------------------------------------------------------
                | Bestaande gebruiker synchroniseren
                |--------------------------------------------------------------------------
                */

                $changed = false;


                /*
                |--------------------------------------------------------------------------
                | Facebook ID koppelen
                |--------------------------------------------------------------------------
                */

                if (blank($user->facebook_id)) {
                    $user->facebook_id = $facebookId;
                    $changed = true;
                }


                /*
                |--------------------------------------------------------------------------
                | Facebook avatar synchroniseren
                |--------------------------------------------------------------------------
                */

                if (
                    filled($avatar) &&
                    $user->facebook_avatar !== $avatar
                ) {
                    $user->facebook_avatar = $avatar;
                    $changed = true;
                }


                /*
                |--------------------------------------------------------------------------
                | Naam aanvullen
                |--------------------------------------------------------------------------
                */

                if (
                    blank($user->name) &&
                    $displayName !== ''
                ) {
                    $user->name = $displayName;
                    $changed = true;
                }


                /*
                |--------------------------------------------------------------------------
                | E-mailverificatie synchroniseren
                |--------------------------------------------------------------------------
                */

                if (! $user->email_verified_at) {
                    $user->email_verified_at = now();
                    $changed = true;
                }


                /*
                |--------------------------------------------------------------------------
                | Wijzigingen opslaan
                |--------------------------------------------------------------------------
                */

                if ($changed) {
                    $user->save();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Welkomstmail via Brevo
            |--------------------------------------------------------------------------
            |
            | Een probleem met Brevo mag Facebook-login niet blokkeren.
            |
            */

            if ($isNewUser) {
                try {
                    $this->brevoMail->send(
                        $user->email,
                        $user->name,
                        'Welkom bij Mashal Automotive',
                        'emails.welcome-account-created',
                        [
                            'user' => $user,
                        ]
                    );
                } catch (Throwable $mailException) {
                    report($mailException);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Gebruiker inloggen
            |--------------------------------------------------------------------------
            */

            Auth::login(
                $user,
                true
            );


            /*
            |--------------------------------------------------------------------------
            | Session vernieuwen
            |--------------------------------------------------------------------------
            */

            request()
                ->session()
                ->regenerate();


            /*
            |--------------------------------------------------------------------------
            | Redirect nieuwe gebruiker
            |--------------------------------------------------------------------------
            */

            if ($isNewUser) {
                return redirect()
                    ->route('account')
                    ->with(
                        'success',
                        'Welkom bij Mashal Automotive. Je account is succesvol aangemaakt met Facebook.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Redirect bestaande gebruiker
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Welkom terug. Je bent succesvol ingelogd met Facebook.'
                );

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Technische fout loggen
            |--------------------------------------------------------------------------
            |
            | De volledige fout wordt opgeslagen in de Laravel/Railway logs,
            | maar wordt bewust niet rechtstreeks aan de gebruiker getoond.
            |
            */

            report($exception);


            /*
            |--------------------------------------------------------------------------
            | Veilige foutmelding
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Facebook-login kon niet worden voltooid. Controleer je Facebook-toestemming en probeer het opnieuw. Blijft het probleem bestaan, neem dan contact op met Mashal Automotive.'
                );
        }
    }
}