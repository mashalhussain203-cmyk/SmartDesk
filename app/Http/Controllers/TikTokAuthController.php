<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class TikTokAuthController extends Controller
{
    /**
     * Stuur de gebruiker door naar TikTok Login Kit.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('tiktok')
            ->redirect();
    }

    /**
     * Verwerk de callback van TikTok.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $tiktokUser = Socialite::driver('tiktok')->user();

            $tiktokId = trim(
                (string) $tiktokUser->getId()
            );

            $name = trim(
                (string) $tiktokUser->getName()
            );

            $nickname = trim(
                (string) $tiktokUser->getNickname()
            );

            $avatar = trim(
                (string) $tiktokUser->getAvatar()
            );

            if ($tiktokId === '') {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'tiktok' => 'TikTok heeft geen geldig gebruikers-ID teruggegeven.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Bestaand TikTok-account
            |--------------------------------------------------------------------------
            */

            $user = User::query()
                ->where('tiktok_id', $tiktokId)
                ->first();

            if ($user !== null) {
                $user->forceFill([
                    'login_provider' => 'tiktok',
                    'tiktok_avatar' => $avatar !== ''
                        ? $avatar
                        : $user->tiktok_avatar,
                ])->save();

                Auth::login(
                    $user,
                    true
                );

                $request->session()->regenerate();

                return $this->redirectAfterLogin($user);
            }

            /*
            |--------------------------------------------------------------------------
            | Nieuwe TikTok-gebruiker
            |--------------------------------------------------------------------------
            |
            | TikTok user.info.basic levert standaard geen e-mailadres mee.
            | Daarom slaan we de TikTok-profielgegevens tijdelijk op in
            | de sessie en vragen we daarna om een e-mailadres.
            |
            */

            Session::put(
                'tiktok_registration',
                [
                    'tiktok_id' => $tiktokId,
                    'name' => $this->resolveDisplayName(
                        $name,
                        $nickname
                    ),
                    'avatar' => $avatar !== ''
                        ? $avatar
                        : null,
                ]
            );

            return redirect()
                ->route('tiktok.complete');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors([
                    'tiktok' => 'Inloggen met TikTok is mislukt. Probeer het opnieuw.',
                ]);
        }
    }

    /**
     * Toon het formulier voor het afronden van TikTok-registratie.
     */
    public function showCompleteRegistration(): View|RedirectResponse
    {
        $registration = Session::get(
            'tiktok_registration'
        );

        if (
            ! is_array($registration)
            || trim(
                (string) ($registration['tiktok_id'] ?? '')
            ) === ''
        ) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'tiktok' => 'De TikTok-aanmeldsessie is verlopen. Log opnieuw in met TikTok.',
                ]);
        }

        return view(
            'auth.tiktok-complete',
            [
                'tiktokName' => $registration['name'] ?? 'TikTok gebruiker',
                'tiktokAvatar' => $registration['avatar'] ?? null,
            ]
        );
    }

    /**
     * Rond de registratie van een nieuwe TikTok-gebruiker af.
     */
    public function completeRegistration(
        Request $request
    ): RedirectResponse {
        $registration = Session::get(
            'tiktok_registration'
        );

        if (
            ! is_array($registration)
            || trim(
                (string) ($registration['tiktok_id'] ?? '')
            ) === ''
        ) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'tiktok' => 'De TikTok-aanmeldsessie is verlopen. Log opnieuw in met TikTok.',
                ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                ],
            ],
            [
                'email.required' => 'Vul je e-mailadres in.',
                'email.email' => 'Vul een geldig e-mailadres in.',
                'email.max' => 'Het e-mailadres mag maximaal 255 tekens bevatten.',
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = Str::lower(
            trim(
                (string) $request->input('email')
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Bescherming tegen onveilige account-koppeling
        |--------------------------------------------------------------------------
        */

        $existingUser = User::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [
                    $email,
                ]
            )
            ->first();

        if ($existingUser !== null) {
            return back()
                ->withErrors([
                    'email' => 'Er bestaat al een account met dit e-mailadres. Log eerst in op dat bestaande account.',
                ])
                ->withInput();
        }

        $tiktokId = trim(
            (string) ($registration['tiktok_id'] ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | Dubbele TikTok-ID voorkomen
        |--------------------------------------------------------------------------
        */

        $existingTikTokUser = User::query()
            ->where('tiktok_id', $tiktokId)
            ->first();

        if ($existingTikTokUser !== null) {
            Session::forget(
                'tiktok_registration'
            );

            $existingTikTokUser->forceFill([
                'login_provider' => 'tiktok',
            ])->save();

            Auth::login(
                $existingTikTokUser,
                true
            );

            $request->session()->regenerate();

            return $this->redirectAfterLogin(
                $existingTikTokUser
            );
        }

        $name = trim(
            (string) ($registration['name'] ?? '')
        );

        if ($name === '') {
            $name = 'TikTok gebruiker';
        }

        $avatar = trim(
            (string) ($registration['avatar'] ?? '')
        );

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Str::random(64),
            'tiktok_id' => $tiktokId,
            'tiktok_avatar' => $avatar !== ''
                ? $avatar
                : null,
            'login_provider' => 'tiktok',
            'is_admin' => false,
            'email_verified_at' => null,
        ]);

        Session::forget(
            'tiktok_registration'
        );

        Auth::login(
            $user,
            true
        );

        $request->session()->regenerate();

        return $this->redirectAfterLogin(
            $user
        );
    }

    /**
     * Bepaal de beste TikTok-weergavenaam.
     */
    private function resolveDisplayName(
        string $name,
        string $nickname
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

        return 'TikTok gebruiker';
    }

    /**
     * Redirect na succesvol inloggen.
     */
    private function redirectAfterLogin(
        User $user
    ): RedirectResponse {
        if ($user->isAdmin()) {
            return redirect()
                ->route('admin.dashboard')
                ->with(
                    'success',
                    'Je bent succesvol ingelogd met TikTok.'
                );
        }

        return redirect()
            ->route('account')
            ->with(
                'success',
                'Je bent succesvol ingelogd met TikTok.'
            );
    }
}