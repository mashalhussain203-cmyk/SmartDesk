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

class LinkedInAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver(
            'linkedin-openid'
        )
            ->scopes([
                'openid',
                'profile',
                'email',
            ])
            ->redirect();
    }

    public function callback(
        Request $request
    ): RedirectResponse {
        try {
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

            $rawUser = is_array(
                $linkedinUser->user ?? null
            )
                ? $linkedinUser->user
                : [];

            $emailVerified = filter_var(
                $rawUser['email_verified'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

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

            if ($email === '') {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'LinkedIn heeft geen e-mailadres beschikbaar gesteld. Geef toegang tot je e-mailadres en probeer opnieuw.'
                    );
            }

            /*
             * We koppelen nooit automatisch op een onbevestigd e-mailadres.
             * De huidige LinkedIn OpenID-flow levert email_verified als claim.
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

            $displayName =
                $name !== ''
                    ? $name
                    : $this->displayNameFromEmail(
                        $email
                    );

            $isNewUser = false;

            $user = User::query()
                ->where(
                    'linkedin_id',
                    $linkedinId
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
             * Beveilig bestaand account tegen automatische herkoppeling
             * naar een ander LinkedIn-profiel.
             */
            if (
                $user &&
                filled(
                    $user->linkedin_id
                ) &&
                (string) $user->linkedin_id !==
                $linkedinId
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

            if (! $user) {
                $user = User::create([
                    'name' =>
                        $displayName,

                    'email' =>
                        $email,

                    'linkedin_id' =>
                        $linkedinId,

                    'linkedin_avatar' =>
                        $avatar !== ''
                            ? $avatar
                            : null,

                    'login_provider' =>
                        'linkedin',

                    'password' =>
                        Hash::make(
                            Str::random(
                                64
                            )
                        ),

                    'email_verified_at' =>
                        now(),

                    'is_admin' =>
                        false,
                ]);

                $isNewUser = true;
            } else {
                $changed = false;

                if (
                    blank(
                        $user->linkedin_id
                    )
                ) {
                    $user->linkedin_id =
                        $linkedinId;

                    $changed =
                        true;
                }

                if (
                    $avatar !== '' &&
                    (string) $user->linkedin_avatar !==
                    $avatar
                ) {
                    $user->linkedin_avatar =
                        $avatar;

                    $changed =
                        true;
                }

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

                if (! $user->email_verified_at) {
                    $user->email_verified_at =
                        now();

                    $changed =
                        true;
                }

                if (
                    (string) $user->login_provider !==
                    'linkedin'
                ) {
                    $user->login_provider =
                        'linkedin';

                    $changed =
                        true;
                }

                if ($changed) {
                    $user->save();
                }
            }

            /*
             * LoginSecurityService leest dit tijdens Laravel's Login-event.
             * De V6 groene succesanimatie luistert naar hetzelfde Login-event.
             */
            $request->merge([
                'login_provider' =>
                    'linkedin',
            ]);

            Auth::login(
                $user,
                true
            );

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
