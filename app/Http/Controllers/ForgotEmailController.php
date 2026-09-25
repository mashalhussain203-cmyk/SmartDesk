<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ForgotEmailController extends Controller
{
    private const FLOW_TTL_MINUTES = 10;
    private const RESULT_TTL_MINUTES = 10;
    private const MAX_CODE_ATTEMPTS = 5;

    public function __construct(
        private readonly BrevoMailService $brevoMail
    ) {
    }

    /**
     * Stap 1: vraag voornaam, achternaam en herstel-e-mailadres.
     */
    public function show(Request $request): View
    {
        $this->clearExpiredResult($request);

        return view('site.forgot-email');
    }

    /**
     * Zoek veilig naar een mogelijk account en verstuur een herstelcode.
     *
     * Belangrijk:
     * - Er wordt nooit verteld of een account wel/niet bestaat.
     * - Ook bij geen match maken we een tijdelijke "dummy flow".
     * - De verificatiecode zelf wordt alleen gehasht opgeslagen.
     */
    public function identify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'recovery_email' => ['required', 'string', 'email:rfc', 'max:255'],
        ]);

        if (
            ! $this->userHasColumn('recovery_email')
            || ! $this->userHasColumn('recovery_email_verified_at')
        ) {
            Log::error(
                'ForgotEmailController: recovery e-mail databasekolommen ontbreken.'
            );

            return back()
                ->withInput($request->only(
                    'first_name',
                    'last_name',
                    'recovery_email'
                ))
                ->withErrors([
                    'recovery_email' =>
                        'Account recovery is nog niet volledig geconfigureerd.',
                ]);
        }

        $firstName = $this->normaliseName($validated['first_name']);
        $lastName = $this->normaliseName($validated['last_name']);
        $recoveryEmail = Str::lower(trim($validated['recovery_email']));

        $rateKey = $this->identifyRateKey(
            $request,
            $recoveryEmail
        );

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()
                ->withInput($request->only(
                    'first_name',
                    'last_name',
                    'recovery_email'
                ))
                ->withErrors([
                    'recovery_email' =>
                        "Te veel pogingen. Probeer over {$seconds} seconden opnieuw.",
                ]);
        }

        RateLimiter::hit($rateKey, 60);

        $request->session()->forget([
            'forgot_email_result',
            'forgot_email_result_expires_at',
        ]);

        $user = $this->findMatchingUser(
            $firstName,
            $lastName,
            $recoveryEmail
        );

        $flowToken = Str::random(64);
        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(self::FLOW_TTL_MINUTES);

        $flow = [
            'user_id' => $user?->getKey(),
            'code_hash' => $user
                ? Hash::make($code)
                : Hash::make(Str::random(32)),
            'attempts' => 0,
            'recovery_email' => $recoveryEmail,
            'created_at' => now()->timestamp,
            'expires_at' => $expiresAt->timestamp,
        ];

        $this->storeFlow(
            $flowToken,
            $flow
        );

        $request->session()->put(
            'forgot_email_flow_token',
            $flowToken
        );

        /*
         * Alleen wanneer er echt een match is, versturen we een code.
         * De response naar de browser blijft expres hetzelfde bij wel/geen match.
         */
        if ($user) {
            try {
                $this->sendRecoveryCode(
                    $recoveryEmail,
                    $code,
                    $user
                );
            } catch (Throwable $exception) {
                Log::error(
                    'Versturen forgot-email recovery code mislukt.',
                    [
                        'user_id' => $user->getKey(),
                        'exception' => $exception->getMessage(),
                    ]
                );
            }
        }

        return redirect()
            ->route('email.forgot.verify')
            ->with(
                'status',
                'Als de gegevens overeenkomen met een account, is er een verificatiecode naar het hersteladres gestuurd.'
            );
    }

    /**
     * Stap 2: toon het formulier voor de 6-cijferige code.
     */
    public function verifyForm(Request $request): View|RedirectResponse
    {
        $flowToken = $request->session()->get(
            'forgot_email_flow_token'
        );

        if (! is_string($flowToken) || $flowToken === '') {
            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Start de account recovery opnieuw.',
                ]);
        }

        $flow = $this->getFlow($flowToken);

        if (! $flow || $this->flowExpired($flow)) {
            $this->forgetFlow($flowToken);

            $request->session()->forget(
                'forgot_email_flow_token'
            );

            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Deze recovery-sessie is verlopen. Probeer opnieuw.',
                ]);
        }

        return view('site.forgot-email-verify', [
            'maskedRecoveryEmail' =>
                $this->maskEmail(
                    (string) ($flow['recovery_email'] ?? '')
                ),
        ]);
    }

    /**
     * Controleer de 6-cijferige recoverycode.
     */
    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $flowToken = $request->session()->get(
            'forgot_email_flow_token'
        );

        if (! is_string($flowToken) || $flowToken === '') {
            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Start de account recovery opnieuw.',
                ]);
        }

        $rateKey = $this->verifyRateKey(
            $request,
            $flowToken
        );

        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->withErrors([
                'code' =>
                    "Te veel verificatiepogingen. Probeer over {$seconds} seconden opnieuw.",
            ]);
        }

        RateLimiter::hit($rateKey, 600);

        $flow = $this->getFlow($flowToken);

        if (! $flow || $this->flowExpired($flow)) {
            $this->forgetFlow($flowToken);

            $request->session()->forget(
                'forgot_email_flow_token'
            );

            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Deze recovery-sessie is verlopen. Probeer opnieuw.',
                ]);
        }

        $attempts = (int) ($flow['attempts'] ?? 0);

        if ($attempts >= self::MAX_CODE_ATTEMPTS) {
            $this->forgetFlow($flowToken);

            $request->session()->forget(
                'forgot_email_flow_token'
            );

            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Te veel onjuiste codes. Start de recovery opnieuw.',
                ]);
        }

        $flow['attempts'] = $attempts + 1;

        $isValidCode =
            ! empty($flow['user_id'])
            && Hash::check(
                (string) $validated['code'],
                (string) ($flow['code_hash'] ?? '')
            );

        if (! $isValidCode) {
            $this->storeFlow(
                $flowToken,
                $flow
            );

            return back()
                ->withErrors([
                    'code' =>
                        'De code is ongeldig of verlopen.',
                ]);
        }

        $user = User::query()->find(
            $flow['user_id']
        );

        if (
            ! $user
            || $user->recovery_email_verified_at === null
            || Str::lower(
                trim(
                    (string) $user->recovery_email
                )
            ) !== Str::lower(
                trim(
                    (string) ($flow['recovery_email'] ?? '')
                )
            )
        ) {
            $this->forgetFlow($flowToken);

            $request->session()->forget(
                'forgot_email_flow_token'
            );

            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Deze recovery-sessie is niet meer geldig.',
                ]);
        }

        /*
         * Verificatie is gelukt.
         * Nu mag het account-e-mailadres worden getoond.
         */
        $request->session()->put(
            'forgot_email_result',
            [
                'user_id' => $user->getKey(),
                'email' => $user->email,
                'name' => $user->name,
                'verified_at' => now()->timestamp,
            ]
        );

        $request->session()->put(
            'forgot_email_result_expires_at',
            now()->addMinutes(
                self::RESULT_TTL_MINUTES
            )->timestamp
        );

        $this->forgetFlow($flowToken);

        $request->session()->forget(
            'forgot_email_flow_token'
        );

        RateLimiter::clear($rateKey);

        return redirect()
            ->route('email.forgot.result')
            ->with(
                'success',
                'Je account is gevonden.'
            );
    }

    /**
     * Verstuur een nieuwe code voor een bestaande recovery-flow.
     */
    public function resend(Request $request): RedirectResponse
    {
        $flowToken = $request->session()->get(
            'forgot_email_flow_token'
        );

        if (! is_string($flowToken) || $flowToken === '') {
            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Start de account recovery opnieuw.',
                ]);
        }

        $flow = $this->getFlow($flowToken);

        if (! $flow || $this->flowExpired($flow)) {
            $this->forgetFlow($flowToken);

            $request->session()->forget(
                'forgot_email_flow_token'
            );

            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Deze recovery-sessie is verlopen. Probeer opnieuw.',
                ]);
        }

        $rateKey =
            'forgot-email:resend:'
            . hash(
                'sha256',
                $request->ip()
                . '|'
                . $flowToken
            );

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->withErrors([
                'code' =>
                    "Wacht {$seconds} seconden voordat je opnieuw een code aanvraagt.",
            ]);
        }

        RateLimiter::hit($rateKey, 300);

        /*
         * Ook bij een dummy-flow geven we exact dezelfde browserresponse.
         */
        if (! empty($flow['user_id'])) {
            $user = User::query()->find(
                $flow['user_id']
            );

            if (
                $user
                && $user->recovery_email_verified_at !== null
                && Str::lower(
                    trim(
                        (string) $user->recovery_email
                    )
                ) === Str::lower(
                    trim(
                        (string) ($flow['recovery_email'] ?? '')
                    )
                )
            ) {
                $newCode = (string) random_int(
                    100000,
                    999999
                );

                $flow['code_hash'] =
                    Hash::make($newCode);

                $flow['attempts'] = 0;

                $flow['expires_at'] =
                    now()
                        ->addMinutes(
                            self::FLOW_TTL_MINUTES
                        )
                        ->timestamp;

                $this->storeFlow(
                    $flowToken,
                    $flow
                );

                try {
                    $this->sendRecoveryCode(
                        (string) $flow['recovery_email'],
                        $newCode,
                        $user
                    );
                } catch (Throwable $exception) {
                    Log::error(
                        'Opnieuw versturen forgot-email code mislukt.',
                        [
                            'user_id' => $user->getKey(),
                            'exception' => $exception->getMessage(),
                        ]
                    );
                }
            }
        }

        return back()->with(
            'status',
            'Als de recovery-sessie geldig is, is er een nieuwe code verstuurd.'
        );
    }

    /**
     * Stap 3: toon het gevonden account na succesvolle verificatie.
     */
    public function result(Request $request): View|RedirectResponse
    {
        $this->clearExpiredResult($request);

        $result = $request->session()->get(
            'forgot_email_result'
        );

        if (
            ! is_array($result)
            || empty($result['email'])
        ) {
            return redirect()
                ->route('email.forgot')
                ->withErrors([
                    'recovery_email' =>
                        'Start de account recovery opnieuw.',
                ]);
        }

        return view('site.forgot-email-result', [
            'recoveredEmail' =>
                (string) $result['email'],

            'recoveredName' =>
                (string) ($result['name'] ?? ''),
        ]);
    }

    /**
     * Zoek het account zonder informatie naar buiten te lekken.
     *
     * Ondersteunt:
     * - users.first_name + users.last_name, als die kolommen bestaan;
     * - anders users.name met "Voornaam Achternaam".
     */
    private function findMatchingUser(
        string $firstName,
        string $lastName,
        string $recoveryEmail
    ): ?User {
        $query = User::query()
            ->whereNotNull(
                'recovery_email_verified_at'
            )
            ->whereRaw(
                'LOWER(TRIM(recovery_email)) = ?',
                [$recoveryEmail]
            );

        if (
            $this->userHasColumn('first_name')
            && $this->userHasColumn('last_name')
        ) {
            $query
                ->whereRaw(
                    'LOWER(TRIM(first_name)) = ?',
                    [$firstName]
                )
                ->whereRaw(
                    'LOWER(TRIM(last_name)) = ?',
                    [$lastName]
                );
        } else {
            $fullName =
                trim(
                    $firstName
                    . ' '
                    . $lastName
                );

            $query->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [$fullName]
            );
        }

        return $query->first();
    }

    /**
     * Verstuur de 6-cijferige recoverycode via Brevo.
     */
    private function sendRecoveryCode(
        string $recoveryEmail,
        string $code,
        ?User $user = null
    ): void {
        $this->brevoMail->send(
            $recoveryEmail,
            (string) ($user?->name ?? 'Mashal Studio gebruiker'),
            'Mashal Studio account recovery',
            'emails.recovery-code',
            [
                'user' => $user,
                'code' => $code,
                'minutes' => self::FLOW_TTL_MINUTES,
                'purpose' => 'forgot_email',
            ]
        );
    }

    /**
     * Sla de recovery-flow in de cache op.
     */
    private function storeFlow(
        string $flowToken,
        array $flow
    ): void {
        $expiresAt =
            (int) ($flow['expires_at'] ?? 0);

        if ($expiresAt <= now()->timestamp) {
            return;
        }

        Cache::put(
            $this->flowCacheKey(
                $flowToken
            ),
            $flow,
            now()->setTimestamp(
                $expiresAt
            )
        );
    }

    /**
     * Lees recovery-flow uit cache.
     */
    private function getFlow(
        string $flowToken
    ): ?array {
        $flow = Cache::get(
            $this->flowCacheKey(
                $flowToken
            )
        );

        return is_array($flow)
            ? $flow
            : null;
    }

    /**
     * Verwijder recovery-flow.
     */
    private function forgetFlow(
        string $flowToken
    ): void {
        Cache::forget(
            $this->flowCacheKey(
                $flowToken
            )
        );
    }

    /**
     * Cache-key bevat nooit het ruwe flowtoken.
     */
    private function flowCacheKey(
        string $flowToken
    ): string {
        return
            'forgot-email:flow:'
            . hash(
                'sha256',
                $flowToken
            );
    }

    /**
     * Is de recovery-flow verlopen?
     */
    private function flowExpired(
        array $flow
    ): bool {
        return
            (int) ($flow['expires_at'] ?? 0)
            <= now()->timestamp;
    }

    /**
     * Rate limit voor de identify-stap.
     */
    private function identifyRateKey(
        Request $request,
        string $recoveryEmail
    ): string {
        return
            'forgot-email:identify:'
            . hash(
                'sha256',
                $request->ip()
                . '|'
                . $recoveryEmail
            );
    }

    /**
     * Rate limit voor de codecheck.
     */
    private function verifyRateKey(
        Request $request,
        string $flowToken
    ): string {
        return
            'forgot-email:verify:'
            . hash(
                'sha256',
                $request->ip()
                . '|'
                . $flowToken
            );
    }

    /**
     * Normaliseer voor-/achternaam voor veilige vergelijking.
     */
    private function normaliseName(
        string $value
    ): string {
        $value =
            preg_replace(
                '/\s+/u',
                ' ',
                trim($value)
            ) ?? trim($value);

        return Str::lower($value);
    }

    /**
     * Masker een hersteladres voor de verify-pagina.
     *
     * voorbeeld:
     * mashal@example.com -> m*****@example.com
     */
    private function maskEmail(
        string $email
    ): string {
        if (! str_contains($email, '@')) {
            return '••••••';
        }

        [$local, $domain] =
            explode('@', $email, 2);

        if ($local === '') {
            return '••••••@' . $domain;
        }

        return
            Str::substr($local, 0, 1)
            . str_repeat(
                '•',
                max(
                    3,
                    min(
                        8,
                        Str::length($local) - 1
                    )
                )
            )
            . '@'
            . $domain;
    }

    /**
     * Cache Schema::hasColumn-resultaten binnen dezelfde request.
     */
    private function userHasColumn(
        string $column
    ): bool {
        static $columns = [];

        if (! array_key_exists(
            $column,
            $columns
        )) {
            $columns[$column] =
                Schema::hasColumn(
                    'users',
                    $column
                );
        }

        return $columns[$column];
    }

    /**
     * Wis een verlopen succesvol recovery-resultaat.
     */
    private function clearExpiredResult(
        Request $request
    ): void {
        $expiresAt =
            (int) $request->session()->get(
                'forgot_email_result_expires_at',
                0
            );

        if (
            $expiresAt > 0
            && $expiresAt <= now()->timestamp
        ) {
            $request->session()->forget([
                'forgot_email_result',
                'forgot_email_result_expires_at',
            ]);
        }
    }
}
