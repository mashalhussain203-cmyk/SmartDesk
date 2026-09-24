<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorChallengeController extends Controller
{
    /**
     * Toon de 2FA-challenge tijdens het inloggen.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            $this->clearPendingLogin($request);

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'De beveiligde login is verlopen. Log opnieuw in.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 2FA niet meer actief?
        |--------------------------------------------------------------------------
        |
        | Als de gebruiker inmiddels geen actieve Authenticator meer heeft,
        | kunnen we de login direct afronden.
        |
        */

        if (
            $user->two_factor_confirmed_at === null ||
            empty($user->two_factor_secret)
        ) {
            return $this->completeLogin(
                $request,
                $user
            );
        }

        return view(
            'auth.two-factor-challenge',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * Controleer een 6-cijferige Authenticator-code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
                'code' => [
                    'required',
                    'digits:6',
                ],
            ],
            [
                'code.required' =>
                    'Voer de 6-cijferige verificatiecode in.',

                'code.digits' =>
                    'De verificatiecode moet uit precies 6 cijfers bestaan.',
            ]
        );

        $user = $this->pendingUser($request);

        if (! $user) {
            $this->clearPendingLogin($request);

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'De beveiligde login is verlopen. Log opnieuw in.'
                );
        }

        if (
            $user->two_factor_confirmed_at === null ||
            empty($user->two_factor_secret)
        ) {
            return $this->completeLogin(
                $request,
                $user
            );
        }

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            (string) $user->two_factor_secret,
            (string) $data['code']
        );

        if (! $valid) {
            return back()
                ->withInput()
                ->withErrors(
                    [
                        'code' =>
                            'De verificatiecode is ongeldig of verlopen. Gebruik de nieuwste code uit je Authenticator-app.',
                    ]
                );
        }

        return $this->completeLogin(
            $request,
            $user
        );
    }

    /**
     * Controleer een recovery code.
     *
     * Een gebruikte recovery code wordt direct verwijderd en kan dus
     * niet opnieuw worden gebruikt.
     */
    public function verifyRecoveryCode(
        Request $request
    ): RedirectResponse {
        $data = $request->validate(
            [
                'recovery_code' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ],
            [
                'recovery_code.required' =>
                    'Voer een herstelcode in.',
            ]
        );

        $user = $this->pendingUser($request);

        if (! $user) {
            $this->clearPendingLogin($request);

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'De beveiligde login is verlopen. Log opnieuw in.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Ingevoerde recovery code normaliseren
        |--------------------------------------------------------------------------
        */

        $submittedCode = strtoupper(
            trim(
                (string) $data['recovery_code']
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Recovery codes ophalen
        |--------------------------------------------------------------------------
        |
        | User.php hoort two_factor_recovery_codes als encrypted:array te casten.
        |
        */

        $recoveryCodes = $user->two_factor_recovery_codes;

        if (! is_array($recoveryCodes)) {
            $recoveryCodes = [];
        }

        $matchingIndex = null;

        foreach ($recoveryCodes as $index => $recoveryCode) {
            $storedCode = strtoupper(
                trim(
                    (string) $recoveryCode
                )
            );

            if (
                $storedCode !== '' &&
                hash_equals(
                    $storedCode,
                    $submittedCode
                )
            ) {
                $matchingIndex = $index;

                break;
            }
        }

        if ($matchingIndex === null) {
            return back()
                ->withInput()
                ->withErrors(
                    [
                        'recovery_code' =>
                            'Deze herstelcode is ongeldig of is al gebruikt.',
                    ]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Gebruikte recovery code verwijderen
        |--------------------------------------------------------------------------
        */

        unset(
            $recoveryCodes[$matchingIndex]
        );

        $user->forceFill(
            [
                'two_factor_recovery_codes' =>
                    array_values($recoveryCodes),
            ]
        )->save();

        return $this->completeLogin(
            $request,
            $user
        );
    }

    /**
     * Annuleer de openstaande 2FA-login.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $this->clearPendingLogin($request);

        return redirect()
            ->route('login')
            ->with(
                'success',
                'De beveiligde login is geannuleerd.'
            );
    }

    /**
     * Haal de gebruiker van de tijdelijke 2FA-login op.
     */
    private function pendingUser(
        Request $request
    ): ?User {
        $userId = $request->session()->get(
            'two_factor.login.user_id'
        );

        if (
            ! is_numeric($userId) ||
            (int) $userId <= 0
        ) {
            return null;
        }

        return User::find(
            (int) $userId
        );
    }

    /**
     * Rond de login definitief af nadat 2FA is geslaagd.
     */
    private function completeLogin(
        Request $request,
        User $user
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Remember-me instelling ophalen
        |--------------------------------------------------------------------------
        */

        $remember = (bool) $request->session()->get(
            'two_factor.login.remember',
            false
        );

        /*
        |--------------------------------------------------------------------------
        | Login-provider ophalen
        |--------------------------------------------------------------------------
        */

        $provider = trim(
            (string) $request->session()->get(
                'two_factor.login.provider',
                'password'
            )
        );

        if ($provider === '') {
            $provider = 'password';
        }

        /*
        |--------------------------------------------------------------------------
        | Definitief inloggen
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            $remember
        );

        /*
        |--------------------------------------------------------------------------
        | Bescherming tegen session fixation
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Login-provider bewaren
        |--------------------------------------------------------------------------
        */

        $user->forceFill(
            [
                'login_provider' => $provider,
            ]
        )->save();

        /*
        |--------------------------------------------------------------------------
        | Tijdelijke 2FA-login opruimen
        |--------------------------------------------------------------------------
        */

        $this->clearPendingLogin($request);

        /*
        |--------------------------------------------------------------------------
        | Doorsturen naar intended URL
        |--------------------------------------------------------------------------
        |
        | UserController kan vóór de challenge al url.intended hebben gezet voor:
        |
        | - pending image;
        | - admin dashboard;
        | - normale intended route.
        |
        */

        return redirect()
            ->intended(
                $user->is_admin
                    ? route('admin.dashboard')
                    : route('home')
            )
            ->with(
                'success',
                'Beveiligingscontrole geslaagd. Welkom terug, ' .
                Str::of((string) $user->name)->trim() .
                '.'
            );
    }

    /**
     * Verwijder alleen tijdelijke 2FA-loginwaarden.
     *
     * url.intended en pending_image blijven bewust behouden.
     */
    private function clearPendingLogin(
        Request $request
    ): void {
        $request->session()->forget(
            [
                'two_factor.login.user_id',
                'two_factor.login.remember',
                'two_factor.login.provider',
            ]
        );
    }
}