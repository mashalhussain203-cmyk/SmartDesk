<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Naam die in Authenticator-apps wordt weergegeven.
     */
    private const ISSUER = 'Mashal Studio';

    /**
     * Aantal recovery codes dat per set wordt aangemaakt.
     */
    private const RECOVERY_CODE_COUNT = 8;

    /**
     * Toon de Authenticator-beveiligingspagina.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        $pendingSecret = $request->session()->get(
            'two_factor_setup_secret'
        );

        $qrCode = null;

        if (
            is_string($pendingSecret) &&
            $pendingSecret !== '' &&
            $user->two_factor_confirmed_at === null
        ) {
            $google2fa = new Google2FA();

            $qrCode = $google2fa->getQRCodeInline(
                self::ISSUER,
                (string) $user->email,
                $pendingSecret
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Nieuwe recovery codes slechts één keer tonen
        |--------------------------------------------------------------------------
        */

        $newRecoveryCodes = $request->session()->pull(
            'two_factor_new_recovery_codes'
        );

        return view(
            'account.two-factor',
            [
                'user' => $user,
                'qrCode' => $qrCode,
                'pendingSecret' => $pendingSecret,
                'newRecoveryCodes' => $newRecoveryCodes,
            ]
        );
    }

    /**
     * Start een nieuwe Authenticator-setup.
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at !== null) {
            return redirect()
                ->route('two-factor.show')
                ->with(
                    'error',
                    'Authenticator-verificatie is al ingeschakeld.'
                );
        }

        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();

        /*
        |--------------------------------------------------------------------------
        | Secret tijdelijk in de sessie bewaren
        |--------------------------------------------------------------------------
        |
        | Pas na een geldige 6-cijferige code wordt deze secret definitief
        | versleuteld in de database opgeslagen.
        |
        */

        $request->session()->put(
            'two_factor_setup_secret',
            $secret
        );

        return redirect()
            ->route('two-factor.show')
            ->with(
                'success',
                'Scan de QR-code met je Authenticator-app en voer daarna de 6-cijferige code in.'
            );
    }

    /**
     * Bevestig de eerste 6-cijferige Authenticator-code.
     */
    public function confirm(Request $request): RedirectResponse
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

        $user = $request->user();

        $secret = $request->session()->get(
            'two_factor_setup_secret'
        );

        if (
            ! is_string($secret) ||
            $secret === ''
        ) {
            return redirect()
                ->route('two-factor.show')
                ->with(
                    'error',
                    'De Authenticator-instelling is verlopen. Start opnieuw.'
                );
        }

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            $secret,
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

        $recoveryCodes = $this->generateRecoveryCodes();

        /*
        |--------------------------------------------------------------------------
        | Authenticator activeren
        |--------------------------------------------------------------------------
        |
        | User.php hoort deze velden als encrypted / encrypted:array te casten.
        |
        */

        $user->forceFill(
            [
                'two_factor_secret' => $secret,
                'two_factor_recovery_codes' => $recoveryCodes,
                'two_factor_confirmed_at' => now(),
            ]
        )->save();

        $request->session()->forget(
            'two_factor_setup_secret'
        );

        /*
        |--------------------------------------------------------------------------
        | Recovery codes éénmalig laten zien
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'two_factor_new_recovery_codes',
            $recoveryCodes
        );

        return redirect()
            ->route('two-factor.show')
            ->with(
                'success',
                'Authenticator-verificatie is succesvol ingeschakeld. Bewaar je herstelcodes op een veilige plek.'
            );
    }

    /**
     * Annuleer een nog niet bevestigde Authenticator-setup.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(
            'two_factor_setup_secret'
        );

        return redirect()
            ->route('two-factor.show')
            ->with(
                'success',
                'Het instellen van Authenticator-verificatie is geannuleerd.'
            );
    }

    /**
     * Maak een nieuwe set recovery codes.
     */
    public function regenerateRecoveryCodes(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if (
            $user->two_factor_confirmed_at === null ||
            empty($user->two_factor_secret)
        ) {
            return redirect()
                ->route('two-factor.show')
                ->with(
                    'error',
                    'Authenticator-verificatie is niet ingeschakeld.'
                );
        }

        if ($this->userHasPassword($user)) {
            $data = $request->validate(
                [
                    'password' => [
                        'required',
                        'string',
                    ],
                ],
                [
                    'password.required' =>
                        'Vul je huidige wachtwoord in.',
                ]
            );

            if (
                ! Hash::check(
                    (string) $data['password'],
                    (string) $user->password
                )
            ) {
                return back()
                    ->withErrors(
                        [
                            'password' =>
                                'Het ingevoerde wachtwoord is niet correct.',
                        ]
                    );
            }
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->forceFill(
            [
                'two_factor_recovery_codes' => $recoveryCodes,
            ]
        )->save();

        $request->session()->put(
            'two_factor_new_recovery_codes',
            $recoveryCodes
        );

        return redirect()
            ->route('two-factor.show')
            ->with(
                'success',
                'Nieuwe herstelcodes zijn aangemaakt. Je oude herstelcodes werken niet meer.'
            );
    }

    /**
     * Schakel Authenticator-verificatie uit.
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($this->userHasPassword($user)) {
            $data = $request->validate(
                [
                    'password' => [
                        'required',
                        'string',
                    ],
                ],
                [
                    'password.required' =>
                        'Vul je huidige wachtwoord in.',
                ]
            );

            if (
                ! Hash::check(
                    (string) $data['password'],
                    (string) $user->password
                )
            ) {
                return back()
                    ->withErrors(
                        [
                            'password' =>
                                'Het ingevoerde wachtwoord is niet correct.',
                        ]
                    );
            }
        }

        $user->forceFill(
            [
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        )->save();

        $request->session()->forget(
            [
                'two_factor_setup_secret',
                'two_factor_new_recovery_codes',
            ]
        );

        return redirect()
            ->route('two-factor.show')
            ->with(
                'success',
                'Authenticator-verificatie is uitgeschakeld.'
            );
    }

    /**
     * Controleer of deze gebruiker een lokaal wachtwoord heeft.
     */
    private function userHasPassword(
        object $user
    ): bool {
        return is_string($user->password)
            && trim($user->password) !== '';
    }

    /**
     * Genereer veilige recovery codes.
     *
     * Voorbeeld:
     * ABCD1234-EFGH5678
     */
    private function generateRecoveryCodes(): array
    {
        $codes = [];

        for (
            $i = 0;
            $i < self::RECOVERY_CODE_COUNT;
            $i++
        ) {
            $codes[] =
                strtoupper(Str::random(8)) .
                '-' .
                strtoupper(Str::random(8));
        }

        return $codes;
    }
}
