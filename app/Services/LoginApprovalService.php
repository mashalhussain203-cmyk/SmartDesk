<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class LoginApprovalService
{
    public const TTL_MINUTES = 5;

    /**
     * Alleen starten wanneer er een andere, recent actieve database-sessie
     * voor dezelfde gebruiker bestaat.
     *
     * Zo vergrendelen we iemand nooit wanneer er nergens anders meer een
     * ingelogd apparaat beschikbaar is.
     */
    public function hasOtherActiveSession(
        User $user,
        Request $request
    ): bool {
        if (
            (string) config(
                'session.driver'
            ) !== 'database'
        ) {
            return false;
        }

        if (! $this->challengeTableExists()) {
            return false;
        }

        $sessionTable =
            (string) config(
                'session.table',
                'sessions'
            );

        try {
            if (! Schema::hasTable($sessionTable)) {
                return false;
            }

            $lifetimeMinutes =
                max(
                    1,
                    (int) config(
                        'session.lifetime',
                        120
                    )
                );

            $activeAfter =
                now()
                    ->subMinutes(
                        $lifetimeMinutes
                    )
                    ->timestamp;

            return DB::table(
                $sessionTable
            )
                ->where(
                    'user_id',
                    $user->getKey()
                )
                ->where(
                    'last_activity',
                    '>=',
                    $activeAfter
                )
                ->where(
                    'id',
                    '!=',
                    $request
                        ->session()
                        ->getId()
                )
                ->exists();
        } catch (Throwable) {
            /*
             * De approval-laag mag een normale login nooit kapot maken als de
             * sessietabel tijdelijk niet beschikbaar of anders ingericht is.
             */
            return false;
        }
    }

    /**
     * Maak een nummermatching challenge.
     *
     * Het juiste nummer zelf staat niet als plain text in de database.
     * Alleen de drie zichtbare keuzemogelijkheden en een hash worden bewaard.
     */
    public function createChallenge(
        User $user,
        Request $request,
        bool $remember,
        string $provider = 'password'
    ): array {
        [
            $expectedNumber,
            $options,
        ] = $this->generateNumberChallenge();

        $id =
            (string) Str::uuid();

        $expiresAt =
            now()->addMinutes(
                self::TTL_MINUTES
            );

        DB::table(
            'login_approval_challenges'
        )->insert([
            'id' =>
                $id,

            'user_id' =>
                $user->getKey(),

            'origin_session_id' =>
                $request
                    ->session()
                    ->getId(),

            'status' =>
                'pending',

            'number_hash' =>
                Hash::make(
                    (string) $expectedNumber
                ),

            'options' =>
                json_encode(
                    $options,
                    JSON_THROW_ON_ERROR
                ),

            'remember' =>
                $remember,

            'login_provider' =>
                $provider,

            'requested_ip' =>
                $request->ip(),

            'requested_user_agent' =>
                mb_substr(
                    (string) $request->userAgent(),
                    0,
                    1000
                ),

            'approved_by_session_id' =>
                null,

            'approved_at' =>
                null,

            'expires_at' =>
                $expiresAt,

            'consumed_at' =>
                null,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        return [
            'id' =>
                $id,

            'number' =>
                $expectedNumber,

            'expires_at' =>
                $expiresAt->timestamp,
        ];
    }

    /**
     * Markeer verlopen pending challenges.
     */
    public function expirePendingForUser(
        int|string $userId
    ): void {
        if (! $this->challengeTableExists()) {
            return;
        }

        DB::table(
            'login_approval_challenges'
        )
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'status',
                'pending'
            )
            ->where(
                'expires_at',
                '<=',
                now()
            )
            ->update([
                'status' =>
                    'expired',

                'updated_at' =>
                    now(),
            ]);
    }

    public function challengeTableExists(): bool
    {
        static $exists = null;

        if ($exists === null) {
            $exists =
                Schema::hasTable(
                    'login_approval_challenges'
                );
        }

        return $exists;
    }

    /**
     * Drie unieke tweecijferige nummers, waarvan één het juiste nummer is.
     *
     * @return array{0:int,1:array<int,int>}
     */
    private function generateNumberChallenge(): array
    {
        $numbers = [];

        while (count($numbers) < 3) {
            $candidate =
                random_int(
                    10,
                    99
                );

            if (
                ! in_array(
                    $candidate,
                    $numbers,
                    true
                )
            ) {
                $numbers[] =
                    $candidate;
            }
        }

        $expected =
            $numbers[
                random_int(
                    0,
                    2
                )
            ];

        /*
         * De volgorde hoeft geen beveiligingsgeheim te zijn; het juiste
         * nummer wordt uitsluitend op het wachtende apparaat getoond.
         */
        shuffle($numbers);

        return [
            $expected,
            $numbers,
        ];
    }
}
