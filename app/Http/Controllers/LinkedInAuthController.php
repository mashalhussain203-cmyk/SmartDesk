<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Throwable;

class LinkedInAuthController extends Controller
{
    /**
     * Stuur de gebruiker naar LinkedIn.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver(
            'linkedin-openid'
        )->redirect();
    }

    /**
     * Verwerk de callback van LinkedIn.
     */
    public function callback(
        Request $request
    ): RedirectResponse {
        try {
            /**
             * LinkedIn OpenID retourneert een Socialite Two\User-object.
             *
             * @var SocialiteUser $linkedinUser
             */
            $linkedinUser = Socialite::driver(
                'linkedin-openid'
            )->user();

            $linkedinId = trim(
                (string) $linkedinUser->getId()
            );

            $email = strtolower(
                trim(
                    (string) $linkedinUser->getEmail()
                )
            );

            $name = trim(
                (string) $linkedinUser->getName()
            );

            $avatar = trim(
                (string) $linkedinUser->getAvatar()
            );

            /**
             * Ruwe OpenID-claims van LinkedIn.
             */
            $rawUser = $linkedinUser->getRaw();

            $emailVerified = filter_var(
                $rawUser['email_verified'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            /**
             * LinkedIn moet een geldige gebruikers-ID teruggeven.
             */
            if ($linkedinId === '') {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'LinkedIn kon je account niet correct identificeren. Probeer opnieuw.'
                    );
            }

            /**
             * Een e-mailadres is nodig om het Mashal-account
             * veilig te kunnen koppelen.
             */
            if ($email === '') {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'LinkedIn heeft geen e-mailadres beschikbaar gesteld. Geef LinkedIn toestemming om je e-mailadres te delen en probeer opnieuw.'
                    );
            }

            /**
             * Koppel een bestaand account alleen wanneer LinkedIn
             * aangeeft dat het e-mailadres bevestigd is.
             */
            if (! $emailVerified) {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'LinkedIn kon je e-mailadres niet bevestigen. Gebruik een andere loginmethode of controleer je LinkedIn-account.'
                    );
            }

            $displayName = $name !== ''
                ? $name
                : $this->displayNameFromEmail(
                    $email
                );

            /**
             * Zoek eerst naar een bestaande LinkedIn-koppeling.
             */
            $user = User::query()
                ->where(
                    'linkedin_id',
                    $linkedinId
                )
                ->first();

            /**
             * Geen LinkedIn-koppeling gevonden:
             * probeer het bevestigde e-mailadres.
             */
            if (! $user) {
                $user = User::query()
                    ->where(
                        'email',
                        $email
                    )
                    ->first();
            }

            /**
             * Een bestaand Mashal-account mag niet automatisch
             * aan een ander LinkedIn-profiel worden gekoppeld.
             */
            if (
                $user &&
                filled($user->linkedin_id) &&
                (string) $user->linkedin_id !== $linkedinId
            ) {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Dit Mashal-account is al gekoppeld aan een ander LinkedIn-account.'
                    );
            }

            $isNewUser = false;

            /**
             * Nieuwe gebruiker aanmaken.
             */
            if (! $user) {
                $user = User::create([
                    'name' => $displayName,

                    'email' => $email,

                    'linkedin_id' => $linkedinId,

                    'linkedin_avatar' => $avatar !== ''
                        ? $avatar
                        : null,

                    'login_provider' => 'linkedin',

                    /**
                     * OAuth-gebruikers krijgen een willekeurig intern
                     * wachtwoord dat zij niet hoeven te kennen.
                     */
                    'password' => Hash::make(
                        Str::random(64)
                    ),

                    'email_verified_at' => now(),

                    'is_admin' => false,
                ]);

                $isNewUser = true;
            } else {
                /**
                 * Bestaand account synchroniseren.
                 */
                $changed = false;

                if (blank($user->linkedin_id)) {
                    $user->linkedin_id = $linkedinId;

                    $changed = true;
                }

                if (
                    $avatar !== '' &&
                    (string) $user->linkedin_avatar !== $avatar
                ) {
                    $user->linkedin_avatar = $avatar;

                    $changed = true;
                }

                /**
                 * Bestaande lokale naam niet overschrijven.
                 */
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

                if (
                    (string) $user->login_provider !== 'linkedin'
                ) {
                    $user->login_provider = 'linkedin';

                    $changed = true;
                }

                if ($changed) {
                    $user->save();
                }
            }

            /**
             * LoginSecurityService leest de provider tijdens
             * Laravel's Login-event.
             *
             * Dit moet vóór Auth::login() gebeuren.
             */
            $request->merge([
                'login_provider' => 'linkedin',
            ]);

            /**
             * Inloggen.
             *
             * Hierdoor wordt ook het Laravel Login-event uitgevoerd,
             * waarop je securityservice en succesanimatie kunnen reageren.
             */
            Auth::login(
                $user,
                true
            );

            /**
             * Bescherming tegen session fixation.
             */
            $request
                ->session()
                ->regenerate();

            if ($isNewUser) {
                return redirect()
                    ->route('account')
                    ->with(
                        'success',
                        'Welkom bij Mashal Studio. Je account is succesvol aangemaakt met LinkedIn.'
                    );
            }

            return redirect()
                ->route('account')
                ->with(
                    'success',
                    'Welkom terug. Je bent succesvol ingelogd met LinkedIn.'
                );
        } catch (Throwable $exception) {
            report(
                $exception
            );

            $this->forgetLoginSecurityBrowserContext(
                $request
            );

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Inloggen met LinkedIn is niet gelukt. Probeer het opnieuw.'
                );
        }
    }

    /**
     * Maak een nette naam van het gedeelte vóór @.
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

    /**
     * Verwijder tijdelijke browser/security-informatie
     * wanneer de OAuth-login mislukt.
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