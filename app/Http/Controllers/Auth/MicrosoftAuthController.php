<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use Throwable;

class MicrosoftAuthController extends Controller
{
    /**
     * Stuur de bezoeker door naar Microsoft om in te loggen.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('microsoft')
            ->redirect();
    }

    /**
     * Verwerk de callback van Microsoft.
     */
    public function callback(): RedirectResponse
    {
        try {
            $microsoftUser = Socialite::driver('microsoft')->user();
        } catch (Throwable $e) {
            Log::warning('Microsoft OAuth callback mislukt.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return $this->backToLogin(
                'Microsoft-login is mislukt. Probeer het opnieuw.'
            );
        }

        $microsoftId = $this->normalizeMicrosoftId(
            $microsoftUser->getId()
        );

        $email = $this->normalizeEmail(
            $microsoftUser->getEmail()
        );

        if ($microsoftId === null) {
            return $this->backToLogin(
                'Microsoft heeft geen geldig account-ID teruggegeven.'
            );
        }

        if ($email === null) {
            return $this->backToLogin(
                'Microsoft heeft geen bruikbaar e-mailadres teruggegeven.'
            );
        }

        try {
            $user = DB::transaction(
                function () use (
                    $microsoftUser,
                    $microsoftId,
                    $email
                ): User {
                    /*
                    |--------------------------------------------------------------------------
                    | 1. Reeds gekoppeld Microsoft-account
                    |--------------------------------------------------------------------------
                    */

                    $user = User::query()
                        ->where('microsoft_id', $microsoftId)
                        ->lockForUpdate()
                        ->first();

                    if ($user instanceof User) {
                        return $user;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 2. Bestaand Mashal Studio-account met hetzelfde e-mailadres
                    |--------------------------------------------------------------------------
                    |
                    | Automatisch koppelen staat standaard UIT. Dit voorkomt dat
                    | een social login stilzwijgend aan een bestaand lokaal
                    | account wordt gekoppeld.
                    |
                    */

                    $existingUser = User::query()
                        ->whereRaw('LOWER(email) = ?', [$email])
                        ->lockForUpdate()
                        ->first();

                    if ($existingUser instanceof User) {
                        if (! $this->autoLinkByEmailEnabled()) {
                            throw new RuntimeException(
                                'MICROSOFT_EMAIL_ALREADY_EXISTS'
                            );
                        }

                        $existingUser->forceFill([
                            'microsoft_id' => $microsoftId,
                        ]);

                        $existingUser->save();

                        return $existingUser;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 3. Nieuw Mashal Studio-account
                    |--------------------------------------------------------------------------
                    */

                    return $this->createMicrosoftUser(
                        $microsoftUser,
                        $microsoftId,
                        $email
                    );
                }
            );
        } catch (Throwable $e) {
            if (
                $e instanceof RuntimeException
                && $e->getMessage() === 'MICROSOFT_EMAIL_ALREADY_EXISTS'
            ) {
                return $this->backToLogin(
                    'Er bestaat al een Mashal Studio-account met dit '
                    .'e-mailadres. Log eerst in met je bestaande inlogmethode.'
                );
            }

            Log::error('Microsoft-account kon niet worden verwerkt.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'microsoft_id' => $microsoftId,
            ]);

            return $this->backToLogin(
                'Je Microsoft-account kon niet worden gekoppeld. '
                .'Probeer het opnieuw.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Laravel-login voltooien
        |--------------------------------------------------------------------------
        */

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended(
            $this->afterLoginPath()
        );
    }

    /**
     * Maak een nieuwe lokale gebruiker aan vanuit Microsoft.
     */
    private function createMicrosoftUser(
        SocialiteUser $microsoftUser,
        string $microsoftId,
        string $email
    ): User {
        $name = $this->resolveName(
            $microsoftUser,
            $email
        );

        $attributes = [
            'name' => $name,
            'email' => $email,

            /*
             * Microsoft-gebruikers loggen met OAuth in.
             * We maken toch een willekeurig lokaal wachtwoord aan zodat de
             * password-kolom geldig blijft zonder een bekend wachtwoord te
             * introduceren.
             */
            'password' => Hash::make(
                Str::random(64)
            ),

            'microsoft_id' => $microsoftId,
        ];

        /*
        |--------------------------------------------------------------------------
        | Optioneel e-mail direct als geverifieerd behandelen
        |--------------------------------------------------------------------------
        */

        if (
            $this->markEmailVerifiedEnabled()
            && Schema::hasColumn('users', 'email_verified_at')
        ) {
            $attributes['email_verified_at'] = now();
        }

        $user = new User();

        $user->forceFill($attributes);

        $user->save();

        return $user;
    }

    /**
     * Bepaal een bruikbare gebruikersnaam.
     */
    private function resolveName(
        SocialiteUser $microsoftUser,
        string $email
    ): string {
        $name = trim(
            (string) $microsoftUser->getName()
        );

        if ($name !== '') {
            return $name;
        }

        $nickname = trim(
            (string) $microsoftUser->getNickname()
        );

        if ($nickname !== '') {
            return $nickname;
        }

        return Str::headline(
            Str::before($email, '@')
        );
    }

    /**
     * Normaliseer en valideer het Microsoft account-ID.
     */
    private function normalizeMicrosoftId(
        mixed $microsoftId
    ): ?string {
        $microsoftId = trim(
            (string) $microsoftId
        );

        if ($microsoftId === '') {
            return null;
        }

        if (mb_strlen($microsoftId) > 128) {
            return null;
        }

        return $microsoftId;
    }

    /**
     * Normaliseer en valideer het e-mailadres.
     */
    private function normalizeEmail(
        mixed $email
    ): ?string {
        $email = mb_strtolower(
            trim((string) $email)
        );

        if ($email === '') {
            return null;
        }

        if (
            filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            ) === false
        ) {
            return null;
        }

        return $email;
    }

    /**
     * Stuur terug naar de loginpagina met een foutmelding.
     */
    private function backToLogin(
        string $message
    ): RedirectResponse {
        return redirect(
            $this->loginPage()
        )->withErrors([
            'microsoft' => $message,
        ]);
    }

    /**
     * Mag een bestaand account automatisch op e-mail worden gekoppeld?
     */
    private function autoLinkByEmailEnabled(): bool
    {
        return (bool) config(
            'services.microsoft.auto_link_by_email',
            false
        );
    }

    /**
     * Moet een Microsoft e-mailadres direct als geverifieerd gelden?
     */
    private function markEmailVerifiedEnabled(): bool
    {
        return (bool) config(
            'services.microsoft.mark_email_verified',
            false
        );
    }

    /**
     * Pagina na een geslaagde login.
     */
    private function afterLoginPath(): string
    {
        return (string) config(
            'services.microsoft.after_login',
            '/dashboard'
        );
    }

    /**
     * Loginpagina voor fouten.
     */
    private function loginPage(): string
    {
        return (string) config(
            'services.microsoft.login_page',
            '/login'
        );
    }
}
