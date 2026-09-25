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
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class RecoveryEmailController extends Controller
{
    private const FLOW_TTL_MINUTES = 10;
    private const MAX_CODE_ATTEMPTS = 5;

    public function __construct(
        private readonly BrevoMailService $brevoMail
    ) {
    }

    /**
     * Start of vervang een herstel-e-mailadres.
     *
     * Het nieuwe adres wordt pas in users.recovery_email actief nadat
     * de 6-cijferige code succesvol is bevestigd.
     */
    public function send(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user, 401);

        $validated = $request->validate([
            'recovery_email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
            ],
        ]);

        $recoveryEmail = Str::lower(
            trim(
                (string) $validated['recovery_email']
            )
        );

        $primaryEmail = Str::lower(
            trim(
                (string) $user->email
            )
        );

        if ($recoveryEmail === $primaryEmail) {
            return back()
                ->withErrors([
                    'recovery_email' =>
                        'Gebruik een ander herstel-e-mailadres dan je gewone account-e-mailadres.',
                ])
                ->withInput();
        }

        if (
            $user->recovery_email_verified_at !== null
            && Str::lower(
                trim(
                    (string) $user->recovery_email
                )
            ) === $recoveryEmail
        ) {
            return $this->accountRecoveryRedirect()
                ->with(
                    'success',
                    'Dit herstel-e-mailadres is al geverifieerd.'
                );
        }

        $rateKey =
            'recovery-email:send:'
            . $user->getKey()
            . ':'
            . hash(
                'sha256',
                $recoveryEmail
            );

        if (
            RateLimiter::tooManyAttempts(
                $rateKey,
                5
            )
        ) {
            $seconds =
                RateLimiter::availableIn(
                    $rateKey
                );

            return back()
                ->withErrors([
                    'recovery_email' =>
                        "Te veel aanvragen. Probeer over {$seconds} seconden opnieuw.",
                ])
                ->withInput();
        }

        RateLimiter::hit(
            $rateKey,
            300
        );

        $flowToken = Str::random(64);
        $code = (string) random_int(
            100000,
            999999
        );

        $flow = [
            'user_id' => $user->getKey(),
            'pending_email' => $recoveryEmail,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'created_at' => now()->timestamp,
            'expires_at' =>
                now()
                    ->addMinutes(
                        self::FLOW_TTL_MINUTES
                    )
                    ->timestamp,
        ];

        $this->storeFlow(
            $flowToken,
            $flow
        );

        $request->session()->put(
            'recovery_email_flow_token',
            $flowToken
        );

        try {
            $this->sendVerificationCode(
                $user,
                $recoveryEmail,
                $code
            );
        } catch (Throwable $exception) {
            $this->forgetFlow(
                $flowToken
            );

            $request->session()->forget(
                'recovery_email_flow_token'
            );

            Log::error(
                'Recovery e-mail verificatiecode kon niet worden verstuurd.',
                [
                    'user_id' =>
                        $user->getKey(),
                    'exception' =>
                        $exception->getMessage(),
                ]
            );

            report($exception);

            return back()
                ->withErrors([
                    'recovery_email' =>
                        'De verificatiecode kon niet worden verstuurd. Probeer het later opnieuw.',
                ])
                ->withInput();
        }

        return redirect()
            ->route(
                'account.recovery-email.verify'
            )
            ->with(
                'status',
                'We hebben een 6-cijferige verificatiecode naar je nieuwe herstel-e-mailadres gestuurd.'
            );
    }

    /**
     * Toon het verificatieformulier.
     */
    public function verifyForm(
        Request $request
    ): View|RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user, 401);

        $flowToken =
            $request->session()->get(
                'recovery_email_flow_token'
            );

        if (
            ! is_string($flowToken)
            || $flowToken === ''
        ) {
            return $this->accountRecoveryRedirect()
                ->withErrors([
                    'recovery_email' =>
                        'Start de verificatie van je herstel-e-mailadres opnieuw.',
                ]);
        }

        $flow =
            $this->validOwnedFlow(
                $flowToken,
                $user
            );

        if (! $flow) {
            $this->forgetFlow(
                $flowToken
            );

            $request->session()->forget(
                'recovery_email_flow_token'
            );

            return $this->accountRecoveryRedirect()
                ->withErrors([
                    'recovery_email' =>
                        'Deze verificatiesessie is verlopen. Start opnieuw.',
                ]);
        }

        return view(
            'site.recovery-email-verify',
            [
                'maskedRecoveryEmail' =>
                    $this->maskEmail(
                        (string) $flow['pending_email']
                    ),
            ]
        );
    }

    /**
     * Bevestig de 6-cijferige code en activeer het hersteladres.
     */
    public function verify(
        Request $request
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user, 401);

        $validated =
            $request->validate([
                'code' => [
                    'required',
                    'digits:6',
                ],
            ]);

        $flowToken =
            $request->session()->get(
                'recovery_email_flow_token'
            );

        if (
            ! is_string($flowToken)
            || $flowToken === ''
        ) {
            return $this->accountRecoveryRedirect()
                ->withErrors([
                    'recovery_email' =>
                        'Start de verificatie van je herstel-e-mailadres opnieuw.',
                ]);
        }

        $rateKey =
            'recovery-email:verify:'
            . $user->getKey()
            . ':'
            . hash(
                'sha256',
                $flowToken
            );

        if (
            RateLimiter::tooManyAttempts(
                $rateKey,
                10
            )
        ) {
            $seconds =
                RateLimiter::availableIn(
                    $rateKey
                );

            return back()
                ->withErrors([
                    'code' =>
                        "Te veel verificatiepogingen. Probeer over {$seconds} seconden opnieuw.",
                ]);
        }

        RateLimiter::hit(
            $rateKey,
            600
        );

        $flow =
            $this->validOwnedFlow(
                $flowToken,
                $user
            );

        if (! $flow) {
            $this->forgetFlow(
                $flowToken
            );

            $request->session()->forget(
                'recovery_email_flow_token'
            );

            return $this->accountRecoveryRedirect()
                ->withErrors([
                    'recovery_email' =>
                        'Deze verificatiesessie is verlopen. Start opnieuw.',
                ]);
        }

        $attempts =
            (int) ($flow['attempts'] ?? 0);

        if (
            $attempts
            >= self::MAX_CODE_ATTEMPTS
        ) {
            $this->forgetFlow(
                $flowToken
            );

            $request->session()->forget(
                'recovery_email_flow_token'
            );

            return $this->accountRecoveryRedirect()
                ->withErrors([
                    'recovery_email' =>
                        'Te veel onjuiste codes. Start de verificatie opnieuw.',
                ]);
        }

        if (
            ! Hash::check(
                (string) $validated['code'],
                (string) ($flow['code_hash'] ?? '')
            )
        ) {
            $flow['attempts'] =
                $attempts + 1;

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

        $pendingEmail =
            Str::lower(
                trim(
                    (string) $flow['pending_email']
                )
            );

        if (
            $pendingEmail === ''
            || $pendingEmail === Str::lower(
                trim(
                    (string) $user->email
                )
            )
        ) {
            $this->forgetFlow(
                $flowToken
            );

            $request->session()->forget(
                'recovery_email_flow_token'
            );

            return $this->accountRecoveryRedirect()
                ->withErrors([
                    'recovery_email' =>
                        'Dit herstel-e-mailadres kan niet worden geactiveerd.',
                ]);
        }

        $oldRecoveryEmail =
            trim(
                (string) ($user->recovery_email ?? '')
            );

        $user->recovery_email =
            $pendingEmail;

        $user->recovery_email_verified_at =
            now();

        $user->save();

        $this->forgetFlow(
            $flowToken
        );

        $request->session()->forget(
            'recovery_email_flow_token'
        );

        RateLimiter::clear(
            $rateKey
        );

        $this->sendSecurityNoticeSafely(
            $user,
            'verified',
            $oldRecoveryEmail,
            $pendingEmail
        );

        return $this->accountRecoveryRedirect()
            ->with(
                'success',
                'Je herstel-e-mailadres is geverifieerd en geactiveerd.'
            );
    }

    /**
     * Verstuur opnieuw een code voor dezelfde lopende verificatie.
     */
    public function resend(
        Request $request
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user, 401);

        $flowToken =
            $request->session()->get(
                'recovery_email_flow_token'
            );

        if (
            ! is_string($flowToken)
            || $flowToken === ''
        ) {
            return $this->accountRecoveryRedirect()
                ->withErrors([
                    'recovery_email' =>
                        'Start de verificatie opnieuw.',
                ]);
        }

        $flow =
            $this->validOwnedFlow(
                $flowToken,
                $user
            );

        if (! $flow) {
            $this->forgetFlow(
                $flowToken
            );

            $request->session()->forget(
                'recovery_email_flow_token'
            );

            return $this->accountRecoveryRedirect()
                ->withErrors([
                    'recovery_email' =>
                        'Deze verificatiesessie is verlopen. Start opnieuw.',
                ]);
        }

        $rateKey =
            'recovery-email:resend:'
            . $user->getKey()
            . ':'
            . hash(
                'sha256',
                $flowToken
            );

        if (
            RateLimiter::tooManyAttempts(
                $rateKey,
                3
            )
        ) {
            $seconds =
                RateLimiter::availableIn(
                    $rateKey
                );

            return back()
                ->withErrors([
                    'code' =>
                        "Wacht {$seconds} seconden voordat je opnieuw een code aanvraagt.",
                ]);
        }

        RateLimiter::hit(
            $rateKey,
            300
        );

        $code =
            (string) random_int(
                100000,
                999999
            );

        $flow['code_hash'] =
            Hash::make(
                $code
            );

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
            $this->sendVerificationCode(
                $user,
                (string) $flow['pending_email'],
                $code
            );
        } catch (Throwable $exception) {
            Log::error(
                'Recovery e-mail verificatiecode opnieuw versturen mislukt.',
                [
                    'user_id' =>
                        $user->getKey(),
                    'exception' =>
                        $exception->getMessage(),
                ]
            );

            report($exception);

            return back()
                ->withErrors([
                    'code' =>
                        'De nieuwe code kon niet worden verstuurd. Probeer het later opnieuw.',
                ]);
        }

        return back()
            ->with(
                'status',
                'Er is een nieuwe verificatiecode verstuurd.'
            );
    }

    /**
     * Verwijder het actieve herstel-e-mailadres.
     */
    public function destroy(
        Request $request
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user, 401);

        $oldRecoveryEmail =
            trim(
                (string) ($user->recovery_email ?? '')
            );

        if ($oldRecoveryEmail === '') {
            return $this->accountRecoveryRedirect()
                ->with(
                    'success',
                    'Er is geen herstel-e-mailadres om te verwijderen.'
                );
        }

        $user->recovery_email = null;
        $user->recovery_email_verified_at = null;
        $user->save();

        $flowToken =
            $request->session()->pull(
                'recovery_email_flow_token'
            );

        if (
            is_string($flowToken)
            && $flowToken !== ''
        ) {
            $this->forgetFlow(
                $flowToken
            );
        }

        $this->sendSecurityNoticeSafely(
            $user,
            'removed',
            $oldRecoveryEmail,
            ''
        );

        return $this->accountRecoveryRedirect()
            ->with(
                'success',
                'Je herstel-e-mailadres is verwijderd.'
            );
    }

    private function sendVerificationCode(
        User $user,
        string $recoveryEmail,
        string $code
    ): void {
        $this->brevoMail->send(
            $recoveryEmail,
            (string) $user->name,
            'Bevestig je herstel-e-mailadres - Mashal Studio',
            'emails.recovery-code',
            [
                'user' => $user,
                'code' => $code,
                'minutes' =>
                    self::FLOW_TTL_MINUTES,
                'purpose' =>
                    'recovery_email_verification',
            ]
        );
    }

    private function sendSecurityNoticeSafely(
        User $user,
        string $action,
        string $oldRecoveryEmail,
        string $newRecoveryEmail
    ): void {
        try {
            $this->brevoMail->send(
                (string) $user->email,
                (string) $user->name,
                'Beveiligingswijziging: herstel-e-mailadres - Mashal Studio',
                'emails.recovery-email-updated',
                [
                    'user' => $user,
                    'action' => $action,
                    'oldRecoveryEmail' =>
                        $oldRecoveryEmail,
                    'newRecoveryEmail' =>
                        $newRecoveryEmail,
                ]
            );
        } catch (Throwable $exception) {
            Log::warning(
                'Recovery e-mail beveiligingsmelding kon niet worden verstuurd.',
                [
                    'user_id' =>
                        $user->getKey(),
                    'action' =>
                        $action,
                    'exception' =>
                        $exception->getMessage(),
                ]
            );

            report($exception);
        }
    }

    private function validOwnedFlow(
        string $flowToken,
        User $user
    ): ?array {
        $flow =
            $this->getFlow(
                $flowToken
            );

        if (
            ! $flow
            || $this->flowExpired(
                $flow
            )
            || (int) (
                $flow['user_id']
                ?? 0
            ) !== (int) $user->getKey()
            || empty(
                $flow['pending_email']
            )
        ) {
            return null;
        }

        return $flow;
    }

    private function storeFlow(
        string $flowToken,
        array $flow
    ): void {
        $expiresAt =
            (int) (
                $flow['expires_at']
                ?? 0
            );

        if (
            $expiresAt
            <= now()->timestamp
        ) {
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

    private function getFlow(
        string $flowToken
    ): ?array {
        $flow =
            Cache::get(
                $this->flowCacheKey(
                    $flowToken
                )
            );

        return is_array($flow)
            ? $flow
            : null;
    }

    private function forgetFlow(
        string $flowToken
    ): void {
        Cache::forget(
            $this->flowCacheKey(
                $flowToken
            )
        );
    }

    private function flowCacheKey(
        string $flowToken
    ): string {
        return
            'recovery-email:flow:'
            . hash(
                'sha256',
                $flowToken
            );
    }

    private function flowExpired(
        array $flow
    ): bool {
        return
            (int) (
                $flow['expires_at']
                ?? 0
            )
            <= now()->timestamp;
    }

    private function maskEmail(
        string $email
    ): string {
        $email = trim(
            $email
        );

        if (
            ! str_contains(
                $email,
                '@'
            )
        ) {
            return '••••••';
        }

        [
            $local,
            $domain,
        ] = explode(
            '@',
            $email,
            2
        );

        $localLength =
            mb_strlen(
                $local
            );

        if ($localLength <= 1) {
            $maskedLocal = '•';
        } elseif ($localLength === 2) {
            $maskedLocal =
                mb_substr(
                    $local,
                    0,
                    1
                )
                . '•';
        } else {
            $maskedLocal =
                mb_substr(
                    $local,
                    0,
                    1
                )
                . str_repeat(
                    '•',
                    min(
                        6,
                        $localLength - 2
                    )
                )
                . mb_substr(
                    $local,
                    -1
                );
        }

        return
            $maskedLocal
            . '@'
            . $domain;
    }

    private function accountRecoveryRedirect(): RedirectResponse
    {
        return redirect()
            ->to(
                route('account')
                . '#recovery'
            );
    }
}
