<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class TelegramAuthController extends Controller
{
    /**
     * Hoe lang Telegram login-data geldig mag blijven.
     */
    private const TELEGRAM_AUTH_MAX_AGE = 600;

    /**
     * Hoe lang een onafgeronde Telegram-registratie geldig blijft.
     */
    private const PENDING_REGISTRATION_MAX_AGE = 900;

    /**
     * Telegram callback verwerken.
     *
     * De klassieke Telegram Login Widget stuurt onder andere:
     * id, first_name, last_name, username, photo_url, auth_date en hash.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $telegramData = $this->validateTelegramLogin($request);

            $telegramId = trim((string) ($telegramData['id'] ?? ''));

            if ($telegramId === '') {
                throw new RuntimeException('Telegram user ID ontbreekt.');
            }

            /*
            |--------------------------------------------------------------------------
            | Bestaand Telegram-account
            |--------------------------------------------------------------------------
            */

            $user = User::query()
                ->where('telegram_id', $telegramId)
                ->first();

            if ($user) {
                $this->updateTelegramProfile($user, $telegramData);

                $user->forceFill([
                    'login_provider' => 'telegram',
                ])->save();

                Auth::login($user, true);
                $request->session()->regenerate();

                return redirect()->intended(
                    config('services.telegram.after_login', '/account')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Nieuwe Telegram-gebruiker
            |--------------------------------------------------------------------------
            |
            | De klassieke Telegram Login Widget levert geen e-mailadres mee.
            | Daarom bewaren we de geverifieerde Telegram-identiteit tijdelijk
            | in de sessie en laten we de gebruiker daarna een e-mailadres invullen.
            |
            */

            $request->session()->put('telegram_pending_registration', [
                'telegram_id' => $telegramId,
                'first_name' => $this->nullableString($telegramData['first_name'] ?? null),
                'last_name' => $this->nullableString($telegramData['last_name'] ?? null),
                'username' => $this->nullableString($telegramData['username'] ?? null),
                'photo_url' => $this->nullableString($telegramData['photo_url'] ?? null),
                'issued_at' => now()->timestamp,
            ]);

            return redirect()->route('auth.telegram.complete');
        } catch (Throwable $exception) {
            Log::warning('Telegram login callback mislukt.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->to(config('services.telegram.login_page', '/login'))
                ->withErrors([
                    'telegram' => 'Telegram-login is mislukt. Probeer het opnieuw.',
                ]);
        }
    }

    /**
     * Toon het formulier voor een nieuwe Telegram-gebruiker.
     */
    public function showCompleteRegistration(Request $request): View|RedirectResponse
    {
        $pending = $this->pendingRegistration($request);

        if ($pending === null) {
            return redirect()
                ->to(config('services.telegram.login_page', '/login'))
                ->withErrors([
                    'telegram' => 'Je Telegram-login is verlopen. Probeer opnieuw.',
                ]);
        }

        $suggestedName = trim(
            implode(' ', array_filter([
                $pending['first_name'] ?? null,
                $pending['last_name'] ?? null,
            ]))
        );

        return view('auth.telegram-complete', [
            'telegram' => $pending,
            'suggestedName' => $suggestedName !== ''
                ? $suggestedName
                : ($pending['username'] ?? 'Telegram gebruiker'),
        ]);
    }

    /**
     * Rond een nieuwe Telegram-registratie af.
     */
    public function completeRegistration(Request $request): RedirectResponse
    {
        $pending = $this->pendingRegistration($request);

        if ($pending === null) {
            return redirect()
                ->to(config('services.telegram.login_page', '/login'))
                ->withErrors([
                    'telegram' => 'Je Telegram-login is verlopen. Probeer opnieuw.',
                ]);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email'),
            ],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Beschermen tegen dubbele Telegram-koppeling
            |--------------------------------------------------------------------------
            */

            $existingTelegramUser = User::query()
                ->where('telegram_id', $pending['telegram_id'])
                ->first();

            if ($existingTelegramUser) {
                $request->session()->forget('telegram_pending_registration');

                $existingTelegramUser->forceFill([
                    'login_provider' => 'telegram',
                ])->save();

                Auth::login($existingTelegramUser, true);
                $request->session()->regenerate();

                return redirect()->intended(
                    config('services.telegram.after_login', '/account')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Nieuw Mashal-account aanmaken
            |--------------------------------------------------------------------------
            */

            $attributes = [
                'name' => trim((string) $validated['name']),
                'email' => Str::lower(trim((string) $validated['email'])),
                'password' => Hash::make(Str::random(64)),
                'telegram_id' => (string) $pending['telegram_id'],
                'login_provider' => 'telegram',
            ];

            if (
                Schema::hasColumn('users', 'telegram_username')
                && ! empty($pending['username'])
            ) {
                $attributes['telegram_username'] = $pending['username'];
            }

            if (
                Schema::hasColumn('users', 'telegram_avatar')
                && ! empty($pending['photo_url'])
            ) {
                $attributes['telegram_avatar'] = $pending['photo_url'];
            }

            $user = User::query()->create($attributes);

            $request->session()->forget('telegram_pending_registration');

            Auth::login($user, true);
            $request->session()->regenerate();

            /*
            |--------------------------------------------------------------------------
            | E-mailadres nog laten verifiëren
            |--------------------------------------------------------------------------
            |
            | Telegram heeft het handmatig ingevoerde e-mailadres niet geverifieerd.
            | Daarom blijft email_verified_at leeg en gebruiken we de bestaande
            | Mashal e-mailverificatie.
            |
            */

            return redirect()->route('verification.notice');
        } catch (Throwable $exception) {
            Log::error('Telegram registratie afronden mislukt.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'telegram' => 'Telegram-registratie kon niet worden afgerond.',
                ]);
        }
    }

    /**
     * Verifieer de Telegram Login Widget callback cryptografisch.
     *
     * Telegram schrijft voor:
     * secret_key = SHA256(bot_token)
     * expected_hash = HMAC_SHA256(data_check_string, secret_key)
     */
    private function validateTelegramLogin(Request $request): array
    {
        $botToken = trim((string) config('services.telegram.bot_token'));

        if ($botToken === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN ontbreekt.');
        }

        $data = $request->query();

        $receivedHash = strtolower(
            trim((string) ($data['hash'] ?? ''))
        );

        if ($receivedHash === '') {
            throw new RuntimeException('Telegram hash ontbreekt.');
        }

        unset($data['hash']);

        foreach ($data as $key => $value) {
            if (! is_scalar($value) && $value !== null) {
                throw new RuntimeException('Ongeldige Telegram callback-data.');
            }

            if ($value === null) {
                unset($data[$key]);
                continue;
            }

            $data[$key] = (string) $value;
        }

        ksort($data, SORT_STRING);

        $dataCheckString = implode(
            "\n",
            array_map(
                static fn (string $key, string $value): string => $key . '=' . $value,
                array_keys($data),
                array_values($data)
            )
        );

        $secretKey = hash(
            'sha256',
            $botToken,
            true
        );

        $calculatedHash = hash_hmac(
            'sha256',
            $dataCheckString,
            $secretKey
        );

        if (! hash_equals($calculatedHash, $receivedHash)) {
            throw new RuntimeException('Telegram callback-handtekening is ongeldig.');
        }

        $authDate = filter_var(
            $data['auth_date'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (! $authDate) {
            throw new RuntimeException('Telegram auth_date ontbreekt of is ongeldig.');
        }

        $age = now()->timestamp - $authDate;

        if ($age < -60) {
            throw new RuntimeException('Telegram auth_date ligt in de toekomst.');
        }

        if ($age > self::TELEGRAM_AUTH_MAX_AGE) {
            throw new RuntimeException('Telegram login-data is verlopen.');
        }

        return $data;
    }

    /**
     * Haal een geldige onafgeronde Telegram-registratie uit de sessie.
     */
    private function pendingRegistration(Request $request): ?array
    {
        $pending = $request->session()->get(
            'telegram_pending_registration'
        );

        if (! is_array($pending)) {
            return null;
        }

        $telegramId = trim(
            (string) ($pending['telegram_id'] ?? '')
        );

        $issuedAt = filter_var(
            $pending['issued_at'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($telegramId === '' || ! $issuedAt) {
            $request->session()->forget('telegram_pending_registration');

            return null;
        }

        if (
            now()->timestamp - $issuedAt
            > self::PENDING_REGISTRATION_MAX_AGE
        ) {
            $request->session()->forget('telegram_pending_registration');

            return null;
        }

        return $pending;
    }

    /**
     * Werk optionele Telegram-profielvelden bij als de kolommen bestaan.
     */
    private function updateTelegramProfile(
        User $user,
        array $telegramData
    ): void {
        $updates = [];

        if (Schema::hasColumn('users', 'telegram_username')) {
            $updates['telegram_username'] = $this->nullableString(
                $telegramData['username'] ?? null
            );
        }

        if (Schema::hasColumn('users', 'telegram_avatar')) {
            $updates['telegram_avatar'] = $this->nullableString(
                $telegramData['photo_url'] ?? null
            );
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }
    }

    /**
     * Normaliseer een optionele stringwaarde.
     */
    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
