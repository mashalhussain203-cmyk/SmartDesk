<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class TikTokAuthController extends Controller
{
    /**
     * Redirect naar TikTok Login Kit.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('tiktok')->redirect();
    }

    /**
     * TikTok OAuth callback verwerken.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $tiktokUser = Socialite::driver('tiktok')->user();

            $tiktokId = trim((string) $tiktokUser->getId());
            $name = trim((string) $tiktokUser->getName());
            $nickname = trim((string) $tiktokUser->getNickname());
            $avatar = trim((string) $tiktokUser->getAvatar());

            if ($tiktokId === '') {
                $this->forgetLoginSecurityBrowserContext(
                    $request
                );

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'tiktok' => 'TikTok heeft geen geldig gebruikers-ID teruggegeven.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Bestaande TikTok-gebruiker
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
                ]);

                $user->save();

                /*
                |--------------------------------------------------------------------------
                | Loginmethode vóór Auth::login beschikbaar maken
                |--------------------------------------------------------------------------
                |
                | Laravel vuurt het Login-event tijdens Auth::login() af.
                | De LoginSecurityService kan hierdoor direct zien dat deze
                | succesvolle login via TikTok plaatsvindt.
                |
                */

                $request->merge([
                    'login_provider' => 'tiktok',
                ]);

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
            | TikTok user.info.basic levert doorgaans geen e-mailadres op.
            | Daarom bewaren we de TikTok-data tijdelijk in de sessie en laten
            | we de gebruiker zijn Mashal-registratie veilig afronden.
            |
            */

            Session::put('tiktok_registration', [
                'tiktok_id' => $tiktokId,
                'name' => $this->resolveDisplayName(
                    $name,
                    $nickname
                ),
                'avatar' => $avatar !== ''
                    ? $avatar
                    : null,
            ]);

            return redirect()
                ->route('tiktok.complete');
        } catch (Throwable $exception) {
            report($exception);

            /*
            |--------------------------------------------------------------------------
            | Mislukte OAuth-flow opruimen
            |--------------------------------------------------------------------------
            |
            | Er heeft geen succesvolle Laravel-login plaatsgevonden.
            | We verwijderen daarom alleen de tijdelijke login-securitycontext,
            | zodat deze niet per ongeluk aan een latere login wordt gekoppeld.
            |
            */

            $this->forgetLoginSecurityBrowserContext(
                $request
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'tiktok' => 'Inloggen met TikTok is mislukt. Probeer het opnieuw.',
                ]);
        }
    }

    /**
     * Formulier tonen waarmee een nieuwe TikTok-gebruiker
     * zijn registratie kan afronden.
     */
    public function showCompleteRegistration(
        Request $request
    ): View|RedirectResponse {
        $registration = Session::get('tiktok_registration');

        if (! is_array($registration)) {
            $this->forgetLoginSecurityBrowserContext(
                $request
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'tiktok' => 'De TikTok-aanmeldsessie is verlopen. Log opnieuw in met TikTok.',
                ]);
        }

        $tiktokId = trim(
            (string) ($registration['tiktok_id'] ?? '')
        );

        if ($tiktokId === '') {
            Session::forget('tiktok_registration');

            $this->forgetLoginSecurityBrowserContext(
                $request
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'tiktok' => 'De TikTok-aanmeldsessie is ongeldig. Log opnieuw in met TikTok.',
                ]);
        }

        return view('auth.tiktok-complete', [
            'tiktokName' => $registration['name'] ?? 'TikTok gebruiker',
            'tiktokAvatar' => $registration['avatar'] ?? null,
        ]);
    }

    /**
     * Registratie van een nieuwe TikTok-gebruiker afronden.
     */
    public function completeRegistration(Request $request): RedirectResponse
    {
        $registration = Session::get('tiktok_registration');

        if (! is_array($registration)) {
            $this->forgetLoginSecurityBrowserContext(
                $request
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'tiktok' => 'De TikTok-aanmeldsessie is verlopen. Log opnieuw in met TikTok.',
                ]);
        }

        $tiktokId = trim(
            (string) ($registration['tiktok_id'] ?? '')
        );

        if ($tiktokId === '') {
            Session::forget('tiktok_registration');

            $this->forgetLoginSecurityBrowserContext(
                $request
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'tiktok' => 'De TikTok-aanmeldsessie is ongeldig. Log opnieuw in met TikTok.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validatie
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
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

        $email = Str::lower(
            trim((string) $validated['email'])
        );

        /*
        |--------------------------------------------------------------------------
        | Bestaand e-mailadres beschermen
        |--------------------------------------------------------------------------
        |
        | TikTok wordt niet automatisch gekoppeld aan een bestaand Mashal-
        | account enkel op basis van een ingevuld e-mailadres.
        |
        */

        $existingEmailUser = User::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [$email]
            )
            ->first();

        if ($existingEmailUser !== null) {
            return back()
                ->withErrors([
                    'email' => 'Er bestaat al een account met dit e-mailadres. Log eerst in op dat bestaande account.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | TikTok-ID opnieuw controleren
        |--------------------------------------------------------------------------
        */

        $existingTikTokUser = User::query()
            ->where('tiktok_id', $tiktokId)
            ->first();

        if ($existingTikTokUser !== null) {
            Session::forget('tiktok_registration');

            $existingTikTokUser->forceFill([
                'login_provider' => 'tiktok',
            ]);

            $existingTikTokUser->save();

            /*
            |--------------------------------------------------------------------------
            | Loginmethode vóór Auth::login beschikbaar maken
            |--------------------------------------------------------------------------
            */

            $request->merge([
                'login_provider' => 'tiktok',
            ]);

            Auth::login(
                $existingTikTokUser,
                true
            );

            $request->session()->regenerate();

            return $this->redirectAfterLogin(
                $existingTikTokUser
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Naam
        |--------------------------------------------------------------------------
        */

        $name = trim(
            (string) ($registration['name'] ?? '')
        );

        if ($name === '') {
            $name = 'TikTok gebruiker';
        }

        /*
        |--------------------------------------------------------------------------
        | Avatar
        |--------------------------------------------------------------------------
        */

        $avatar = trim(
            (string) ($registration['avatar'] ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | Account aanmaken
        |--------------------------------------------------------------------------
        */

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(
                Str::random(64)
            ),
            'tiktok_id' => $tiktokId,
            'tiktok_avatar' => $avatar !== ''
                ? $avatar
                : null,
            'login_provider' => 'tiktok',
            'is_admin' => false,
            'email_verified_at' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Tijdelijke TikTok-sessie verwijderen
        |--------------------------------------------------------------------------
        */

        Session::forget('tiktok_registration');

        /*
        |--------------------------------------------------------------------------
        | Nieuwe gebruiker inloggen
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Loginmethode vóór Auth::login beschikbaar maken
        |--------------------------------------------------------------------------
        |
        | De browser-securitycontext uit de sessie blijft beschikbaar totdat
        | het Laravel Login-event tijdens Auth::login() is verwerkt.
        |
        */

        $request->merge([
            'login_provider' => 'tiktok',
        ]);

        Auth::login(
            $user,
            true
        );

        $request->session()->regenerate();

        return $this->redirectAfterLogin($user);
    }

    /**
     * Beste beschikbare TikTok-naam bepalen.
     */
    private function resolveDisplayName(
        string $name,
        string $nickname
    ): string {
        $name = trim($name);

        if ($name !== '') {
            return $name;
        }

        $nickname = trim($nickname);

        if ($nickname !== '') {
            return $nickname;
        }

        return 'TikTok gebruiker';
    }

    /*
    |--------------------------------------------------------------------------
    | Tijdelijke login-security browsercontext opruimen
    |--------------------------------------------------------------------------
    |
    | Bij een succesvolle login verwijdert LoginSecurityService deze gegevens.
    | Deze helper is alleen bedoeld voor TikTok-flows die stoppen vóórdat
    | Auth::login() succesvol is uitgevoerd.
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


    /**
     * Redirect na succesvolle TikTok-login.
     */
    private function redirectAfterLogin(User $user): RedirectResponse
    {
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
