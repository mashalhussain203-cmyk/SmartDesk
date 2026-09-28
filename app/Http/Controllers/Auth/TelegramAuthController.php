<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Hoe lang Telegram-authenticatiedata geldig mag blijven.
     */
    private const TELEGRAM_AUTH_MAX_AGE = 600;

    /**
     * Hoe lang een onafgeronde Telegram-registratie geldig blijft.
     */
    private const PENDING_REGISTRATION_MAX_AGE = 900;

    /**
     * Hoe lang een Mini App -> browser handoff geldig blijft.
     */
    private const HANDOFF_MAX_AGE = 300;

    /**
     * Telegram Login Widget callback verwerken.
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

            $request->session()->put(
                'telegram_pending_registration',
                $this->pendingTelegramData($telegramData)
            );

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
     * Toon de Telegram Main Mini App.
     */
    public function miniApp(): View
    {
        return view('auth.telegram-mini-app');
    }

    /**
     * Valideer Telegram.WebApp.initData en maak een eenmalige browser-handoff.
     */
    public function miniAppAuthenticate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'init_data' => [
                'required',
                'string',
                'max:20000',
            ],
        ]);

        try {
            $initData = $this->validateTelegramMiniAppData(
                (string) $validated['init_data']
            );

            $telegramData = $this->telegramUserFromMiniAppData($initData);

            $telegramId = trim((string) ($telegramData['id'] ?? ''));

            if ($telegramId === '') {
                throw new RuntimeException('Telegram user ID ontbreekt.');
            }

            $user = User::query()
                ->where('telegram_id', $telegramId)
                ->first();

            if ($user) {
                $this->updateTelegramProfile($user, $telegramData);

                $handoffUrl = $this->createMiniAppHandoff(
                    user: $user,
                    telegramData: null
                );

                return response()->json([
                    'ok' => true,
                    'new_user' => false,
                    'handoff_url' => $handoffUrl,
                ]);
            }

            $handoffUrl = $this->createMiniAppHandoff(
                user: null,
                telegramData: $this->pendingTelegramData($telegramData)
            );

            return response()->json([
                'ok' => true,
                'new_user' => true,
                'handoff_url' => $handoffUrl,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Telegram Mini App login mislukt.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Telegram-login kon niet veilig worden bevestigd. Open de Mini App opnieuw.',
            ], 422);
        }
    }

    /**
     * Wissel een eenmalige Mini App handoff om voor een normale browser-sessie.
     */
    public function handoff(Request $request, string $token): RedirectResponse
    {
        $token = trim($token);

        if ($token === '' || strlen($token) > 128) {
            return $this->failedHandoffRedirect();
        }

        try {
            $result = DB::transaction(function () use ($token): array {
                $tokenHash = hash('sha256', $token);

                $handoff = DB::table('telegram_login_handoffs')
                    ->where('token_hash', $tokenHash)
                    ->lockForUpdate()
                    ->first();

                if (! $handoff) {
                    throw new RuntimeException('Telegram handoff niet gevonden.');
                }

                if ($handoff->used_at !== null) {
                    throw new RuntimeException('Telegram handoff is al gebruikt.');
                }

                if (now()->greaterThan($handoff->expires_at)) {
                    throw new RuntimeException('Telegram handoff is verlopen.');
                }

                DB::table('telegram_login_handoffs')
                    ->where('id', $handoff->id)
                    ->update([
                        'used_at' => now(),
                        'updated_at' => now(),
                    ]);

                return [
                    'user_id' => $handoff->user_id,
                    'telegram_data' => $handoff->telegram_data,
                    'intended_url' => $handoff->intended_url,
                ];
            });

            if ($result['user_id'] !== null) {
                $user = User::query()->find($result['user_id']);

                if (! $user) {
                    throw new RuntimeException('Telegram-gebruiker bestaat niet meer.');
                }

                $user->forceFill([
                    'login_provider' => 'telegram',
                ])->save();

                Auth::login($user, true);
                $request->session()->regenerate();

                return redirect()->to(
                    $this->safeAfterLoginUrl(
                        $result['intended_url'] ?? null
                    )
                );
            }

            $telegramData = json_decode(
                (string) ($result['telegram_data'] ?? ''),
                true
            );

            if (
                ! is_array($telegramData)
                || empty($telegramData['telegram_id'])
            ) {
                throw new RuntimeException('Telegram registratiegegevens ontbreken.');
            }

            $request->session()->regenerate();

            $request->session()->put(
                'telegram_pending_registration',
                $telegramData
            );

            return redirect()->route('auth.telegram.complete');
        } catch (Throwable $exception) {
            Log::warning('Telegram browser handoff mislukt.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return $this->failedHandoffRedirect();
        }
    }

    /**
     * Verifieer de klassieke Telegram Login Widget callback.
     *
     * Voor de klassieke widget:
     * secret_key = SHA256(bot_token)
     * expected_hash = HMAC_SHA256(data_check_string, secret_key)
     */
    private function validateTelegramLogin(Request $request): array
    {
        $botToken = $this->telegramBotToken();

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

        $secretKey = hash('sha256', $botToken, true);

        $calculatedHash = hash_hmac(
            'sha256',
            $dataCheckString,
            $secretKey
        );

        if (! hash_equals($calculatedHash, $receivedHash)) {
            throw new RuntimeException('Telegram callback-handtekening is ongeldig.');
        }

        $this->validateAuthDate($data['auth_date'] ?? null);

        return $data;
    }

    /**
     * Verifieer Telegram.WebApp.initData van de Main Mini App.
     *
     * Telegram Mini Apps gebruiken een andere secret-key-afleiding dan
     * de klassieke Login Widget:
     *
     * secret_key = HMAC_SHA256(bot_token, key="WebAppData")
     */
    private function validateTelegramMiniAppData(string $initData): array
    {
        $botToken = $this->telegramBotToken();

        parse_str($initData, $data);

        if (! is_array($data) || $data === []) {
            throw new RuntimeException('Telegram Mini App initData is leeg.');
        }

        $receivedHash = strtolower(
            trim((string) ($data['hash'] ?? ''))
        );

        if ($receivedHash === '') {
            throw new RuntimeException('Telegram Mini App hash ontbreekt.');
        }

        unset($data['hash']);

        foreach ($data as $key => $value) {
            if (! is_scalar($value) && $value !== null) {
                throw new RuntimeException('Ongeldige Telegram Mini App-data.');
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

        $secretKey = hash_hmac(
            'sha256',
            $botToken,
            'WebAppData',
            true
        );

        $calculatedHash = hash_hmac(
            'sha256',
            $dataCheckString,
            $secretKey
        );

        if (! hash_equals($calculatedHash, $receivedHash)) {
            throw new RuntimeException('Telegram Mini App handtekening is ongeldig.');
        }

        $this->validateAuthDate($data['auth_date'] ?? null);

        return $data;
    }

    /**
     * Haal en normaliseer de gebruiker uit het JSON user-veld van initData.
     */
    private function telegramUserFromMiniAppData(array $initData): array
    {
        $user = json_decode(
            (string) ($initData['user'] ?? ''),
            true
        );

        if (! is_array($user)) {
            throw new RuntimeException('Telegram Mini App gebruiker ontbreekt.');
        }

        $telegramId = trim((string) ($user['id'] ?? ''));

        if ($telegramId === '') {
            throw new RuntimeException('Telegram Mini App user ID ontbreekt.');
        }

        return [
            'id' => $telegramId,
            'first_name' => $this->nullableString($user['first_name'] ?? null),
            'last_name' => $this->nullableString($user['last_name'] ?? null),
            'username' => $this->nullableString($user['username'] ?? null),
            'photo_url' => $this->nullableString($user['photo_url'] ?? null),
            'auth_date' => $initData['auth_date'] ?? null,
        ];
    }

    /**
     * Maak een eenmalige, kort geldige handoff-link.
     */
    private function createMiniAppHandoff(
        ?User $user,
        ?array $telegramData
    ): string {
        if (! Schema::hasTable('telegram_login_handoffs')) {
            throw new RuntimeException(
                'telegram_login_handoffs tabel ontbreekt. Voer de migrations uit.'
            );
        }

        $token = Str::random(64);

        DB::table('telegram_login_handoffs')->insert([
            'token_hash' => hash('sha256', $token),
            'user_id' => $user?->id,
            'telegram_data' => $telegramData !== null
                ? json_encode(
                    $telegramData,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
                : null,
            'intended_url' => $this->safeAfterLoginUrl(
                config('services.telegram.after_login', '/account')
            ),
            'expires_at' => now()->addSeconds(self::HANDOFF_MAX_AGE),
            'used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return route('auth.telegram.handoff', [
            'token' => $token,
        ]);
    }

    /**
     * Maak sessiedata voor een nieuwe Telegram-gebruiker.
     */
    private function pendingTelegramData(array $telegramData): array
    {
        return [
            'telegram_id' => trim((string) ($telegramData['id'] ?? '')),
            'first_name' => $this->nullableString($telegramData['first_name'] ?? null),
            'last_name' => $this->nullableString($telegramData['last_name'] ?? null),
            'username' => $this->nullableString($telegramData['username'] ?? null),
            'photo_url' => $this->nullableString($telegramData['photo_url'] ?? null),
            'issued_at' => now()->timestamp,
        ];
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
     * Werk optionele Telegram-profielvelden bij.
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
     * Controleer auth_date tegen replay van oude Telegram-data.
     */
    private function validateAuthDate(mixed $value): void
    {
        $authDate = filter_var(
            $value,
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
    }

    /**
     * Telegram bot token ophalen.
     */
    private function telegramBotToken(): string
    {
        $botToken = trim(
            (string) config('services.telegram.bot_token')
        );

        if ($botToken === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN ontbreekt.');
        }

        return $botToken;
    }

    /**
     * Alleen een veilige interne redirect toestaan.
     */
    private function safeAfterLoginUrl(mixed $value): string
    {
        $url = trim((string) $value);

        if (
            $url === ''
            || ! Str::startsWith($url, '/')
            || Str::startsWith($url, '//')
        ) {
            return '/account';
        }

        return $url;
    }

    /**
     * Veilige foutredirect voor ongeldige/verlopen handoff.
     */
    private function failedHandoffRedirect(): RedirectResponse
    {
        return redirect()
            ->to(config('services.telegram.login_page', '/login'))
            ->withErrors([
                'telegram' => 'Deze Telegram-loginlink is ongeldig of verlopen. Probeer opnieuw.',
            ]);
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
