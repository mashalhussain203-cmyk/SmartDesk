<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoMailService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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


    /*
    |--------------------------------------------------------------------------
    | Facebook OAuth redirect
    |--------------------------------------------------------------------------
    */

    public function redirect(): RedirectResponse
    {
        /** @var FacebookProvider $provider */
        $provider = Socialite::driver(
            'facebook'
        );

        return $provider
            ->scopes([
                'email',
                'public_profile',
            ])
            ->redirect();
    }


    /*
    |--------------------------------------------------------------------------
    | Facebook OAuth callback
    |--------------------------------------------------------------------------
    |
    | Werking:
    |
    | 1. Facebook-profiel ophalen.
    | 2. Facebook-ID en e-mailadres controleren.
    | 3. Eerst zoeken op facebook_id.
    | 4. Daarna zoeken op e-mailadres.
    | 5. Bestaand account veilig koppelen/synchroniseren of nieuw account maken.
    | 6. Nieuwe Facebook-gebruikers ontvangen een welkomstmail via Brevo.
    | 7. login_provider vóór Auth::login() op de request zetten.
    | 8. Auth::login() vuurt Laravel's Login-event af.
    | 9. LoginSecurityService leest de browsercontext uit dezelfde sessie.
    | 10. Daarna vernieuwen we de sessie-ID.
    |
    */

    public function callback(
        Request $request
    ): RedirectResponse {
        try {
            /*
            |--------------------------------------------------------------------------
            | Facebook provider
            |--------------------------------------------------------------------------
            */

            /** @var FacebookProvider $provider */
            $provider = Socialite::driver(
                'facebook'
            );

            $facebookUser = $provider->user();


            /*
            |--------------------------------------------------------------------------
            | Facebook gegevens normaliseren
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

            $avatar = trim(
                (string) $facebookUser->getAvatar()
            );


            /*
            |--------------------------------------------------------------------------
            | Facebook-ID controleren
            |--------------------------------------------------------------------------
            */

            if ($facebookId === '') {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route(
                        'login'
                    )
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
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route(
                        'login'
                    )
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

            $displayName = $this->resolveDisplayName(
                $name,
                $nickname,
                $email
            );


            /*
            |--------------------------------------------------------------------------
            | Gebruiker zoeken
            |--------------------------------------------------------------------------
            */

            $isNewUser = false;

            $user = User::query()
                ->where(
                    'facebook_id',
                    $facebookId
                )
                ->first();

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
            | Conflicterende Facebook-koppeling beschermen
            |--------------------------------------------------------------------------
            |
            | Als het Mashal-account al aan een ander Facebook-ID gekoppeld is,
            | koppelen we dit nieuwe Facebook-account niet automatisch.
            |
            */

            if (
                $user &&
                filled(
                    $user->facebook_id
                ) &&
                (string) $user->facebook_id !==
                $facebookId
            ) {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route(
                        'login'
                    )
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
                    'name' =>
                        $displayName,

                    'email' =>
                        $email,

                    'facebook_id' =>
                        $facebookId,

                    'facebook_avatar' =>
                        $avatar !== ''
                            ? $avatar
                            : null,

                    'login_provider' =>
                        'facebook',

                    /*
                    |--------------------------------------------------------------------------
                    | Intern willekeurig wachtwoord
                    |--------------------------------------------------------------------------
                    |
                    | De gebruiker hoeft dit wachtwoord niet te kennen wanneer
                    | hij via Facebook inlogt.
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
                    | Facebook OAuth bevestigt toegang tot dit Facebook-account
                    |--------------------------------------------------------------------------
                    */

                    'email_verified_at' =>
                        now(),

                    /*
                    |--------------------------------------------------------------------------
                    | Zelfregistratie geeft nooit administratorrechten
                    |--------------------------------------------------------------------------
                    */

                    'is_admin' =>
                        false,
                ]);

                $isNewUser =
                    true;
            } else {
                /*
                |--------------------------------------------------------------------------
                | Bestaande gebruiker synchroniseren
                |--------------------------------------------------------------------------
                */

                $changed = false;


                /*
                |--------------------------------------------------------------------------
                | Facebook-ID koppelen
                |--------------------------------------------------------------------------
                */

                if (
                    blank(
                        $user->facebook_id
                    )
                ) {
                    $user->facebook_id =
                        $facebookId;

                    $changed =
                        true;
                }


                /*
                |--------------------------------------------------------------------------
                | Facebook-avatar bijwerken
                |--------------------------------------------------------------------------
                */

                if (
                    $avatar !== '' &&
                    (string) $user->facebook_avatar !==
                    $avatar
                ) {
                    $user->facebook_avatar =
                        $avatar;

                    $changed =
                        true;
                }


                /*
                |--------------------------------------------------------------------------
                | Naam alleen invullen wanneer lokaal nog geen naam bestaat
                |--------------------------------------------------------------------------
                */

                if (
                    blank(
                        $user->name
                    ) &&
                    $displayName !== ''
                ) {
                    $user->name =
                        $displayName;

                    $changed =
                        true;
                }


                /*
                |--------------------------------------------------------------------------
                | E-mailadres als geverifieerd markeren
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
                    'facebook'
                ) {
                    $user->login_provider =
                        'facebook';

                    $changed =
                        true;
                }


                /*
                |--------------------------------------------------------------------------
                | Alleen opslaan wanneer iets veranderd is
                |--------------------------------------------------------------------------
                */

                if ($changed) {
                    $user->save();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Welkomstmail voor nieuw Facebook-account
            |--------------------------------------------------------------------------
            |
            | Een fout bij het versturen van deze mail mag de succesvolle
            | Facebook-login niet blokkeren.
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
                            'user' =>
                                $user,
                        ]
                    );
                } catch (Throwable $mailException) {
                    report(
                        $mailException
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Login-provider vóór Auth::login beschikbaar maken
            |--------------------------------------------------------------------------
            |
            | Laravel vuurt het Login-event tijdens Auth::login() af.
            | De login-security listener kan hierdoor direct herkennen dat
            | dit een Facebook-login is.
            |
            */

            $request->merge([
                'login_provider' =>
                    'facebook',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Gebruiker inloggen
            |--------------------------------------------------------------------------
            |
            | true behoudt het bestaande remember-login gedrag.
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
            | De Login-listener is op dit moment al uitgevoerd. Daarna
            | vernieuwen we de sessie-ID om session fixation tegen te gaan.
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

            if ($isNewUser) {
                return redirect()
                    ->route(
                        'account'
                    )
                    ->with(
                        'success',
                        'Welkom bij Mashal Automotive. Je account is succesvol aangemaakt met Facebook.'
                    );
            }

            return redirect()
                ->route(
                    'account'
                )
                ->with(
                    'success',
                    'Welkom terug. Je bent succesvol ingelogd met Facebook.'
                );
        } catch (ClientException $exception) {
            /*
            |--------------------------------------------------------------------------
            | Facebook OAuth HTTP-fout loggen
            |--------------------------------------------------------------------------
            |
            | We loggen geen volledige OAuth-responsebody omdat die gevoelige
            | tokeninformatie kan bevatten.
            |
            */

            Log::error(
                'Facebook OAuth token error',
                [
                    'message' =>
                        $exception->getMessage(),

                    'status' =>
                        $exception->hasResponse()
                            ? $exception
                                ->getResponse()
                                ->getStatusCode()
                            : null,
                ]
            );

            $this->forgetLoginSecurityBrowserContext(
                $request
            );

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Facebook-login kon niet worden voltooid. Controleer de Railway logs voor de Facebook-fout.'
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
                    'message' =>
                        $exception->getMessage(),

                    'exception' =>
                        $exception::class,
                ]
            );

            report(
                $exception
            );


            /*
            |--------------------------------------------------------------------------
            | Oude browser-securitycontext opruimen
            |--------------------------------------------------------------------------
            |
            | Omdat de login niet succesvol is afgerond, voorkomen we dat
            | deze tijdelijke context later aan een andere login wordt gekoppeld.
            |
            */

            $this->forgetLoginSecurityBrowserContext(
                $request
            );


            /*
            |--------------------------------------------------------------------------
            | Veilige foutmelding
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'error',
                    'Facebook-login kon niet worden voltooid.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Beste beschikbare weergavenaam bepalen
    |--------------------------------------------------------------------------
    */

    private function resolveDisplayName(
        string $name,
        string $nickname,
        string $email
    ): string {
        $name = trim(
            $name
        );

        if ($name !== '') {
            return $name;
        }

        $nickname = trim(
            $nickname
        );

        if ($nickname !== '') {
            return $nickname;
        }

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
    | Na een succesvolle login verwijdert LoginSecurityService deze context.
    | Deze helper is alleen bedoeld voor Facebook-flows die vóór Auth::login()
    | stoppen of mislukken.
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
