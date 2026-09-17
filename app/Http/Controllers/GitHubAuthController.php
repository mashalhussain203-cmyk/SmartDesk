<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GitHubAuthController extends Controller
{
    /**
     * Brevo mailservice.
     */
    public function __construct(
        private readonly BrevoMailService $brevoMail
    ) {
    }


    /**
     * Stuur de gebruiker door naar GitHub OAuth.
     *
     * De gewenste GitHub-scopes worden ingesteld in config/services.php.
     *
     * Daardoor hoeft hier geen ->scopes(...) call te staan en
     * voorkomen we de Intelephense-waarschuwing:
     *
     * Undefined method 'scopes'.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }


    /**
     * Verwerk de OAuth-callback van GitHub.
     *
     * Flow:
     *
     * 1. GitHub-profiel ophalen.
     * 2. GitHub ID en e-mailadres controleren.
     * 3. Eerst zoeken op github_id.
     * 4. Daarna zoeken op e-mailadres.
     * 5. Bestaand Mashal-account koppelen indien nodig.
     * 6. Of automatisch een nieuw Mashal-account aanmaken.
     * 7. Bij een nieuw account een welkomstmail via Brevo versturen.
     * 8. Gebruiker automatisch inloggen.
     * 9. Sessie regenereren.
     * 10. Doorsturen naar het accountdashboard.
     */
    public function callback(): RedirectResponse
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | GitHub-gebruiker ophalen
            |--------------------------------------------------------------------------
            */

            $githubUser = Socialite::driver('github')->user();


            /*
            |--------------------------------------------------------------------------
            | GitHub-profielgegevens ophalen
            |--------------------------------------------------------------------------
            */

            $githubId = $githubUser->getId();
            $email = $githubUser->getEmail();
            $name = $githubUser->getName();
            $nickname = $githubUser->getNickname();
            $avatar = $githubUser->getAvatar();


            /*
            |--------------------------------------------------------------------------
            | GitHub ID controleren
            |--------------------------------------------------------------------------
            */

            if (!$githubId) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'GitHub heeft geen geldige account-ID teruggegeven.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | E-mailadres controleren
            |--------------------------------------------------------------------------
            |
            | Mashal Automotive heeft een e-mailadres nodig om een gebruiker
            | veilig te koppelen of automatisch te registreren.
            |
            | De benodigde GitHub email-scope staat in config/services.php.
            |
            */

            if (!$email) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'GitHub heeft geen e-mailadres beschikbaar gesteld. Controleer je GitHub e-mailinstellingen en probeer opnieuw.'
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
            | Weergavenaam bepalen
            |--------------------------------------------------------------------------
            |
            | Voorkeursvolgorde:
            |
            | 1. Volledige GitHub-naam
            | 2. GitHub-gebruikersnaam
            | 3. Mashal gebruiker
            |
            */

            $displayName = $name
                ?: $nickname
                ?: 'Mashal gebruiker';


            /*
            |--------------------------------------------------------------------------
            | Bijhouden of dit een nieuw account is
            |--------------------------------------------------------------------------
            |
            | Dit gebruiken we later om te bepalen of de Brevo-welkomstmail
            | verstuurd moet worden.
            |
            */

            $isNewUser = false;


            /*
            |--------------------------------------------------------------------------
            | Eerst zoeken op GitHub ID
            |--------------------------------------------------------------------------
            |
            | Als een account al eerder aan GitHub gekoppeld is, is github_id
            | de primaire manier om de gebruiker terug te vinden.
            |
            */

            $user = User::where(
                'github_id',
                $githubId
            )->first();


            /*
            |--------------------------------------------------------------------------
            | Daarna zoeken op e-mailadres
            |--------------------------------------------------------------------------
            |
            | Als nog geen github_id gekoppeld is, maar hetzelfde e-mailadres
            | al bestaat, gebruiken we dat bestaande Mashal-account.
            |
            | Hierdoor voorkomen we dubbele accounts.
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

                    'name' => $displayName,


                    /*
                    |--------------------------------------------------------------
                    | E-mailadres
                    |--------------------------------------------------------------
                    */

                    'email' => $email,


                    /*
                    |--------------------------------------------------------------
                    | GitHub OAuth
                    |--------------------------------------------------------------
                    */

                    'github_id' => $githubId,

                    'github_avatar' => $avatar,


                    /*
                    |--------------------------------------------------------------
                    | Laatste loginmethode
                    |--------------------------------------------------------------
                    */

                    'login_provider' => 'github',


                    /*
                    |--------------------------------------------------------------
                    | Intern wachtwoord
                    |--------------------------------------------------------------
                    |
                    | De gebruiker logt via GitHub in en kent dit wachtwoord niet.
                    |
                    | Daarom genereren we een sterk willekeurig wachtwoord.
                    |
                    */

                    'password' => Hash::make(
                        Str::random(64)
                    ),


                    /*
                    |--------------------------------------------------------------
                    | E-mailverificatie
                    |--------------------------------------------------------------
                    */

                    'email_verified_at' => now(),


                    /*
                    |--------------------------------------------------------------
                    | Administrator
                    |--------------------------------------------------------------
                    |
                    | Een gebruiker die zich via GitHub registreert krijgt nooit
                    | automatisch administratorrechten.
                    |
                    */

                    'is_admin' => false,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Nieuw account markeren
                |--------------------------------------------------------------------------
                */

                $isNewUser = true;

            } else {

                /*
                |--------------------------------------------------------------------------
                | Bestaand account synchroniseren
                |--------------------------------------------------------------------------
                */

                $changed = false;


                /*
                |--------------------------------------------------------------------------
                | GitHub ID koppelen
                |--------------------------------------------------------------------------
                */

                if ($user->github_id !== $githubId) {
                    $user->github_id = $githubId;

                    $changed = true;
                }


                /*
                |--------------------------------------------------------------------------
                | GitHub-avatar synchroniseren
                |--------------------------------------------------------------------------
                */

                if (
                    $avatar &&
                    $user->github_avatar !== $avatar
                ) {
                    $user->github_avatar = $avatar;

                    $changed = true;
                }


                /*
                |--------------------------------------------------------------------------
                | E-mailadres als geverifieerd markeren
                |--------------------------------------------------------------------------
                */

                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();

                    $changed = true;
                }


                /*
                |--------------------------------------------------------------------------
                | Laatste loginmethode
                |--------------------------------------------------------------------------
                */

                if ($user->login_provider !== 'github') {
                    $user->login_provider = 'github';

                    $changed = true;
                }


                /*
                |--------------------------------------------------------------------------
                | Alleen opslaan als er wijzigingen zijn
                |--------------------------------------------------------------------------
                */

                if ($changed) {
                    $user->save();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Brevo welkomstmail
            |--------------------------------------------------------------------------
            |
            | Alleen nieuwe accounts ontvangen deze mail.
            |
            | Een Brevo-storing mag GitHub-login of registratie nooit blokkeren.
            | Daarom heeft de mailactie een eigen try/catch.
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

                    /*
                    |--------------------------------------------------------------------------
                    | Mailfout loggen
                    |--------------------------------------------------------------------------
                    |
                    | De gebruiker is al succesvol aangemaakt.
                    | Daarom laten we de OAuth-flow gewoon doorgaan.
                    |
                    */

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
            | Sessiebeveiliging
            |--------------------------------------------------------------------------
            |
            | Na een succesvolle authenticatie regenereren we het session ID
            | tegen session fixation.
            |
            */

            request()
                ->session()
                ->regenerate();


            /*
            |--------------------------------------------------------------------------
            | Redirect na succesvolle GitHub-login
            |--------------------------------------------------------------------------
            */

            if ($isNewUser) {
                return redirect()
                    ->route('account')
                    ->with(
                        'success',
                        'Welkom bij Mashal Automotive. Je account is succesvol aangemaakt met GitHub.'
                    );
            }


            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Welkom terug. Je bent succesvol ingelogd met GitHub.'
                );

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Technische OAuth-fout loggen
            |--------------------------------------------------------------------------
            */

            report($exception);


            /*
            |--------------------------------------------------------------------------
            | Veilige foutmelding voor gebruiker
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Inloggen met GitHub is niet gelukt. Probeer het opnieuw.'
                );
        }
    }
}