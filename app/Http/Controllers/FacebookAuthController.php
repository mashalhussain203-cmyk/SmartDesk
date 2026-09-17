<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoMailService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            | Gebruiker zoeken
            |--------------------------------------------------------------------------
            */

            $isNewUser = false;

            $user = User::where(
                'facebook_id',
                $facebookId
            )->first();

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
                    'login_provider' => 'facebook',
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

                if (blank($user->facebook_id)) {
                    $user->facebook_id = $facebookId;
                    $changed = true;
                }

                if (
                    filled($avatar) &&
                    $user->facebook_avatar !== $avatar
                ) {
                    $user->facebook_avatar = $avatar;
                    $changed = true;
                }

                if (
                    blank($user->name) &&
                    $displayName !== ''
                ) {
                    $user->name = $displayName;
                    $changed = true;
                }

                if (! $user->email_verified_at) {
                    $user->email_verified_at = now();
                    $changed = true;
                }

                if ($user->login_provider !== 'facebook') {
                    $user->login_provider = 'facebook';
                    $changed = true;
                }

                if ($changed) {
                    $user->save();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Welkomstmail via Brevo
            |--------------------------------------------------------------------------
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

            request()
                ->session()
                ->regenerate();


            /*
            |--------------------------------------------------------------------------
            | Redirect
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

            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Welkom terug. Je bent succesvol ingelogd met Facebook.'
                );

        } catch (ClientException $exception) {

            /*
            |--------------------------------------------------------------------------
            | Facebook OAuth 400 fout loggen
            |--------------------------------------------------------------------------
            */

            $responseBody = $exception->hasResponse()
                ? (string) $exception->getResponse()->getBody()
                : $exception->getMessage();

            Log::error(
                'Facebook OAuth token error',
                [
                    'message' => $exception->getMessage(),
                    'response' => $responseBody,
                ]
            );

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Facebook-login kon niet worden voltooid. Controleer de Railway logs voor de exacte Facebook-fout.'
                );

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Overige technische fout loggen
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Facebook login error',
                [
                    'message' => $exception->getMessage(),
                    'exception' => get_class($exception),
                ]
            );

            report($exception);

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Facebook-login kon niet worden voltooid.'
                );
        }
    }
}