<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LoginApprovalService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LoginApprovalController extends Controller
{
    public function __construct(
        private readonly LoginApprovalService $approval
    ) {
    }

    /**
     * Wachtpagina op het nieuwe apparaat.
     */
    public function show(
        Request $request
    ): View|RedirectResponse {
        $challenge =
            $this->challengeFromGuestSession(
                $request
            );

        if (! $challenge) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'Deze loginbevestiging is verlopen. Log opnieuw in.',
                ]);
        }

        return view(
            'site.login-approval',
            [
                'approvalNumber' =>
                    (int) $request
                        ->session()
                        ->get(
                            'login_approval.number'
                        ),

                'expiresAt' =>
                    (int) $request
                        ->session()
                        ->get(
                            'login_approval.expires_at'
                        ),

                'maskedEmail' =>
                    $this->maskEmail(
                        (string) (
                            User::query()
                                ->whereKey(
                                    $challenge->user_id
                                )
                                ->value('email')
                            ?? ''
                        )
                    ),
            ]
        );
    }

    /**
     * Pollingstatus voor het wachtende nieuwe apparaat.
     */
    public function status(
        Request $request
    ): JsonResponse {
        $challenge =
            $this->challengeFromGuestSession(
                $request,
                allowTerminalStatus: true
            );

        if (! $challenge) {
            return response()->json([
                'status' =>
                    'expired',
            ]);
        }

        if (
            $challenge->status === 'pending'
            && Carbon::parse(
                $challenge->expires_at
            )->isPast()
        ) {
            DB::table(
                'login_approval_challenges'
            )
                ->where(
                    'id',
                    $challenge->id
                )
                ->where(
                    'status',
                    'pending'
                )
                ->update([
                    'status' =>
                        'expired',

                    'updated_at' =>
                        now(),
                ]);

            return response()->json([
                'status' =>
                    'expired',
            ]);
        }

        return response()->json([
            'status' =>
                (string) $challenge->status,
        ]);
    }

    /**
     * Rond de login af nadat een bestaand ingelogd apparaat heeft goedgekeurd.
     */
    public function complete(
        Request $request
    ): RedirectResponse {
        $challengeId =
            $request
                ->session()
                ->get(
                    'login_approval.challenge_id'
                );

        if (
            ! is_string($challengeId)
            || $challengeId === ''
        ) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'De loginbevestiging is niet meer geldig.',
                ]);
        }

        $result =
            DB::transaction(
                function () use (
                    $challengeId
                ): array {
                    $challenge =
                        DB::table(
                            'login_approval_challenges'
                        )
                            ->where(
                                'id',
                                $challengeId
                            )
                            ->lockForUpdate()
                            ->first();

                    if (
                        ! $challenge
                        || $challenge->status !== 'approved'
                        || $challenge->consumed_at !== null
                        || Carbon::parse(
                            $challenge->expires_at
                        )->isPast()
                    ) {
                        return [
                            'ok' =>
                                false,
                        ];
                    }

                    /** @var User|null $user */
                    $user =
                        User::query()
                            ->find(
                                $challenge->user_id
                            );

                    if (! $user) {
                        return [
                            'ok' =>
                                false,
                        ];
                    }

                    DB::table(
                        'login_approval_challenges'
                    )
                        ->where(
                            'id',
                            $challengeId
                        )
                        ->update([
                            'status' =>
                                'consumed',

                            'consumed_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);

                    return [
                        'ok' =>
                            true,

                        'user' =>
                            $user,

                        'remember' =>
                            (bool) $challenge->remember,

                        'provider' =>
                            (string) (
                                $challenge->login_provider
                                ?: 'password'
                            ),
                    ];
                }
            );

        if (
            ! ($result['ok'] ?? false)
            || ! ($result['user'] ?? null) instanceof User
        ) {
            $this->forgetGuestChallenge(
                $request
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'De loginbevestiging is verlopen of niet goedgekeurd.',
                ]);
        }

        /** @var User $user */
        $user =
            $result['user'];

        $remember =
            (bool) $result['remember'];

        $provider =
            (string) $result['provider'];

        Auth::login(
            $user,
            $remember
        );

        $request
            ->session()
            ->regenerate();

        $user->forceFill([
            'login_provider' =>
                $provider,
        ])->save();

        $this->forgetGuestChallenge(
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Bestaande Authenticator 2FA niet omzeilen
        |--------------------------------------------------------------------------
        |
        | Device approval is een extra beveiligingslaag. Wanneer TOTP actief
        | is blijft de bestaande Authenticator-challenge daarna van kracht.
        |
        */

        if (
            $user->two_factor_confirmed_at !== null
        ) {
            $request
                ->session()
                ->put([
                    'two_factor.login.user_id' =>
                        $user->id,

                    'two_factor.login.remember' =>
                        $remember,

                    'two_factor.login.provider' =>
                        $provider,
                ]);

            Auth::logout();

            $request
                ->session()
                ->regenerate();

            return redirect()
                ->route(
                    'two-factor.challenge'
                )
                ->with(
                    'success',
                    'Apparaat goedgekeurd. Bevestig nu ook je Authenticator-code.'
                );
        }

        return redirect()
            ->intended(
                $user->is_admin
                    ? route(
                        'admin.dashboard'
                    )
                    : route(
                        'home'
                    )
            )
            ->with(
                'success',
                'Je login is goedgekeurd op een bestaand apparaat.'
            );
    }

    /**
     * Annuleer vanaf het nieuwe apparaat.
     */
    public function cancel(
        Request $request
    ): RedirectResponse {
        $challengeId =
            $request
                ->session()
                ->get(
                    'login_approval.challenge_id'
                );

        if (
            is_string($challengeId)
            && $challengeId !== ''
        ) {
            DB::table(
                'login_approval_challenges'
            )
                ->where(
                    'id',
                    $challengeId
                )
                ->where(
                    'status',
                    'pending'
                )
                ->update([
                    'status' =>
                        'cancelled',

                    'updated_at' =>
                        now(),
                ]);
        }

        $this->forgetGuestChallenge(
            $request
        );

        return redirect()
            ->route('login')
            ->with(
                'status',
                'De loginpoging is geannuleerd.'
            );
    }

    /**
     * Polling endpoint voor een reeds ingelogd apparaat.
     */
    public function pending(
        Request $request
    ): JsonResponse {
        /** @var User $user */
        $user =
            $request->user();

        abort_unless(
            $user,
            401
        );

        if (
            ! $this->approval
                ->challengeTableExists()
        ) {
            return response()->json([
                'challenge' =>
                    null,
            ]);
        }

        $this->approval
            ->expirePendingForUser(
                $user->getKey()
            );

        $challenge =
            DB::table(
                'login_approval_challenges'
            )
                ->where(
                    'user_id',
                    $user->getKey()
                )
                ->where(
                    'status',
                    'pending'
                )
                ->where(
                    'expires_at',
                    '>',
                    now()
                )
                ->where(
                    'origin_session_id',
                    '!=',
                    $request
                        ->session()
                        ->getId()
                )
                ->latest(
                    'created_at'
                )
                ->first();

        if (! $challenge) {
            return response()->json([
                'challenge' =>
                    null,
            ]);
        }

        $options =
            json_decode(
                (string) $challenge->options,
                true
            );

        if (! is_array($options)) {
            $options = [];
        }

        $options =
            array_values(
                array_filter(
                    array_map(
                        static fn (
                            mixed $value
                        ): int =>
                            (int) $value,
                        $options
                    ),
                    static fn (
                        int $value
                    ): bool =>
                        $value >= 10
                        && $value <= 99
                )
            );

        return response()->json([
            'challenge' => [
                'id' =>
                    (string) $challenge->id,

                'options' =>
                    $options,

                'device' =>
                    $this->describeDevice(
                        (string) (
                            $challenge
                                ->requested_user_agent
                            ?? ''
                        )
                    ),

                'requested_at' =>
                    Carbon::parse(
                        $challenge->created_at
                    )->format(
                        'd-m-Y H:i'
                    ),

                'expires_in' =>
                    max(
                        0,
                        now()->diffInSeconds(
                            Carbon::parse(
                                $challenge
                                    ->expires_at
                            ),
                            false
                        )
                    ),
            ],
        ]);
    }

    /**
     * Goedkeuren/afwijzen vanaf een bestaand ingelogd apparaat.
     */
    public function respond(
        Request $request,
        string $challenge
    ): JsonResponse {
        /** @var User $user */
        $user =
            $request->user();

        abort_unless(
            $user,
            401
        );

        $validated =
            $request->validate([
                'action' => [
                    'required',
                    'string',
                    'in:approve,reject',
                ],

                'number' => [
                    'nullable',
                    'integer',
                    'between:10,99',
                ],
            ]);

        try {
            $result =
                DB::transaction(
                    function () use (
                        $request,
                        $user,
                        $challenge,
                        $validated
                    ): array {
                        $row =
                            DB::table(
                                'login_approval_challenges'
                            )
                                ->where(
                                    'id',
                                    $challenge
                                )
                                ->where(
                                    'user_id',
                                    $user
                                        ->getKey()
                                )
                                ->lockForUpdate()
                                ->first();

                        if (
                            ! $row
                            || $row->status !== 'pending'
                            || Carbon::parse(
                                $row->expires_at
                            )->isPast()
                        ) {
                            return [
                                'ok' =>
                                    false,

                                'status' =>
                                    'expired',
                            ];
                        }

                        if (
                            hash_equals(
                                (string) $row
                                    ->origin_session_id,
                                $request
                                    ->session()
                                    ->getId()
                            )
                        ) {
                            return [
                                'ok' =>
                                    false,

                                'status' =>
                                    'same_device',
                            ];
                        }

                        if (
                            $validated['action']
                            === 'reject'
                        ) {
                            DB::table(
                                'login_approval_challenges'
                            )
                                ->where(
                                    'id',
                                    $row->id
                                )
                                ->update([
                                    'status' =>
                                        'rejected',

                                    'approved_by_session_id' =>
                                        $request
                                            ->session()
                                            ->getId(),

                                    'updated_at' =>
                                        now(),
                                ]);

                            return [
                                'ok' =>
                                    true,

                                'status' =>
                                    'rejected',
                            ];
                        }

                        $selectedNumber =
                            (int) (
                                $validated['number']
                                ?? 0
                            );

                        $matches =
                            $selectedNumber >= 10
                            && Hash::check(
                                (string) $selectedNumber,
                                (string) $row
                                    ->number_hash
                            );

                        if (! $matches) {
                            /*
                             * Eén fout nummer wijst de poging direct af.
                             * Daardoor kan het ingelogde apparaat niet gaan
                             * raden totdat toevallig het juiste nummer klopt.
                             */
                            DB::table(
                                'login_approval_challenges'
                            )
                                ->where(
                                    'id',
                                    $row->id
                                )
                                ->update([
                                    'status' =>
                                        'rejected',

                                    'approved_by_session_id' =>
                                        $request
                                            ->session()
                                            ->getId(),

                                    'updated_at' =>
                                        now(),
                                ]);

                            return [
                                'ok' =>
                                    false,

                                'status' =>
                                    'wrong_number',
                            ];
                        }

                        DB::table(
                            'login_approval_challenges'
                        )
                            ->where(
                                'id',
                                $row->id
                            )
                            ->update([
                                'status' =>
                                    'approved',

                                'approved_by_session_id' =>
                                    $request
                                        ->session()
                                        ->getId(),

                                'approved_at' =>
                                    now(),

                                'updated_at' =>
                                    now(),
                            ]);

                        return [
                            'ok' =>
                                true,

                            'status' =>
                                'approved',
                        ];
                    }
                );
        } catch (Throwable) {
            return response()->json(
                [
                    'ok' =>
                        false,

                    'status' =>
                        'error',
                ],
                500
            );
        }

        $httpStatus =
            ($result['ok'] ?? false)
                ? 200
                : 422;

        return response()->json(
            $result,
            $httpStatus
        );
    }

    private function challengeFromGuestSession(
        Request $request,
        bool $allowTerminalStatus = false
    ): ?object {
        if (
            ! $this->approval
                ->challengeTableExists()
        ) {
            return null;
        }

        $challengeId =
            $request
                ->session()
                ->get(
                    'login_approval.challenge_id'
                );

        if (
            ! is_string($challengeId)
            || ! Str::isUuid(
                $challengeId
            )
        ) {
            return null;
        }

        $query =
            DB::table(
                'login_approval_challenges'
            )
                ->where(
                    'id',
                    $challengeId
                );

        if (! $allowTerminalStatus) {
            $query->whereIn(
                'status',
                [
                    'pending',
                    'approved',
                ]
            );
        }

        return $query->first();
    }

    private function forgetGuestChallenge(
        Request $request
    ): void {
        $request
            ->session()
            ->forget([
                'login_approval.challenge_id',
                'login_approval.number',
                'login_approval.expires_at',
            ]);
    }

    private function describeDevice(
        string $userAgent
    ): string {
        $userAgent =
            Str::lower(
                $userAgent
            );

        $device =
            match (true) {
                str_contains(
                    $userAgent,
                    'iphone'
                ) =>
                    'iPhone',

                str_contains(
                    $userAgent,
                    'ipad'
                ) =>
                    'iPad',

                str_contains(
                    $userAgent,
                    'android'
                ) =>
                    'Android-apparaat',

                str_contains(
                    $userAgent,
                    'windows'
                ) =>
                    'Windows-pc',

                str_contains(
                    $userAgent,
                    'macintosh'
                ) =>
                    'Mac',

                default =>
                    'Nieuw apparaat',
            };

        $browser =
            match (true) {
                str_contains(
                    $userAgent,
                    'edg/'
                ) =>
                    'Edge',

                str_contains(
                    $userAgent,
                    'chrome/'
                )
                && ! str_contains(
                    $userAgent,
                    'edg/'
                ) =>
                    'Chrome',

                str_contains(
                    $userAgent,
                    'safari/'
                )
                && ! str_contains(
                    $userAgent,
                    'chrome/'
                ) =>
                    'Safari',

                str_contains(
                    $userAgent,
                    'firefox/'
                ) =>
                    'Firefox',

                default =>
                    null,
            };

        return $browser
            ? "{$device} · {$browser}"
            : $device;
    }

    private function maskEmail(
        string $email
    ): string {
        $email =
            trim(
                $email
            );

        if (
            ! str_contains(
                $email,
                '@'
            )
        ) {
            return '';
        }

        [
            $local,
            $domain,
        ] = explode(
            '@',
            $email,
            2
        );

        if ($local === '') {
            return '••••@' . $domain;
        }

        return
            mb_substr(
                $local,
                0,
                1
            )
            . str_repeat(
                '•',
                max(
                    3,
                    min(
                        7,
                        mb_strlen(
                            $local
                        ) - 1
                    )
                )
            )
            . '@'
            . $domain;
    }
}
