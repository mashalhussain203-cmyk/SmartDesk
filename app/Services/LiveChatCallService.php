<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use stdClass;

class LiveChatCallService
{
    public function iceServers(): array
    {
        $servers = [];

        $stunUrls = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) config('live_chat.calls.stun_urls', 'stun:stun.l.google.com:19302'))
        )));

        if ($stunUrls !== []) {
            $servers[] = ['urls' => $stunUrls];
        }

        $turnUrl = trim((string) config('live_chat.calls.turn_url', ''));
        $turnUser = trim((string) config('live_chat.calls.turn_username', ''));
        $turnCredential = (string) config('live_chat.calls.turn_credential', '');

        if ($turnUrl !== '' && $turnUser !== '' && $turnCredential !== '') {
            $servers[] = [
                'urls' => [$turnUrl],
                'username' => $turnUser,
                'credential' => $turnCredential,
            ];
        }

        return $servers;
    }

    public function start(
        int $conversationId,
        string $initiatedBy,
        string $mode,
        array $offer,
        ?int $adminUserId = null
    ): stdClass {
        return DB::transaction(function () use (
            $conversationId,
            $initiatedBy,
            $mode,
            $offer,
            $adminUserId
        ): stdClass {
            $conversation = DB::table('live_chat_conversations')
                ->where('id', $conversationId)
                ->lockForUpdate()
                ->first();

            if (! $conversation) {
                throw new RuntimeException('Het gesprek bestaat niet meer.');
            }

            $this->expireStale($conversationId);

            $active = DB::table('live_chat_calls')
                ->where('conversation_id', $conversationId)
                ->whereIn('status', ['ringing', 'accepted'])
                ->exists();

            if ($active) {
                throw new RuntimeException('Er is al een actieve oproep in dit gesprek.');
            }

            $id = (string) Str::uuid();
            $now = now();

            DB::table('live_chat_calls')->insert([
                'id' => $id,
                'conversation_id' => $conversationId,
                'initiated_by' => $initiatedBy,
                'admin_user_id' => $adminUserId,
                'mode' => $mode,
                'status' => 'ringing',
                'offer_json' => json_encode($offer, JSON_UNESCAPED_SLASHES),
                'answer_json' => null,
                'answered_at' => null,
                'ended_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return DB::table('live_chat_calls')->where('id', $id)->first();
        });
    }

    public function current(int $conversationId): ?stdClass
    {
        $this->expireStale($conversationId);
        $this->expireUnansweredVideoUpgrades($conversationId);

        return DB::table('live_chat_calls')
            ->where('conversation_id', $conversationId)
            ->whereIn('status', ['ringing', 'accepted'])
            ->orderByDesc('created_at')
            ->first();
    }

    public function incomingForAdmin(): ?stdClass
    {
        $cutoff = now()->subSeconds((int) config('live_chat.calls.ring_timeout_seconds', 45));

        DB::table('live_chat_calls')
            ->where('status', 'ringing')
            ->where('created_at', '<', $cutoff)
            ->update([
                'status' => 'missed',
                'ended_at' => now(),
                'updated_at' => now(),
            ]);

        $call = DB::table('live_chat_calls as c')
            ->join('live_chat_conversations as lc', 'lc.id', '=', 'c.conversation_id')
            ->leftJoin('users as u', 'u.id', '=', 'lc.user_id')
            ->where('c.status', 'ringing')
            ->where('c.initiated_by', 'visitor')
            ->orderByDesc('c.created_at')
            ->select('c.*', 'u.name as caller_name', 'lc.id as guest_number')
            ->first();

        if ($call) {
            // Keep incoming-call discovery compatible with SQLite in tests
            // and MySQL/PostgreSQL in production.
            $name = trim((string) ($call->caller_name ?? ''));
            $call->caller_name = $name !== '' ? $name : 'Gast #'.$call->guest_number;
            unset($call->guest_number);
        }

        return $call;
    }

    public function findForConversation(string $callId, int $conversationId): ?stdClass
    {
        return DB::table('live_chat_calls')
            ->where('id', $callId)
            ->where('conversation_id', $conversationId)
            ->first();
    }

    public function answer(string $callId, int $conversationId, array $answer): stdClass
    {
        $call = $this->findForConversation($callId, $conversationId);

        if (! $call || $call->status !== 'ringing') {
            throw new RuntimeException('Deze oproep is niet meer beschikbaar.');
        }

        DB::table('live_chat_calls')
            ->where('id', $callId)
            ->where('conversation_id', $conversationId)
            ->update([
                'status' => 'accepted',
                'answer_json' => json_encode($answer, JSON_UNESCAPED_SLASHES),
                'answered_at' => now(),
                'updated_at' => now(),
            ]);

        return DB::table('live_chat_calls')->where('id', $callId)->first();
    }

    public function decline(string $callId, int $conversationId): void
    {
        DB::table('live_chat_calls')
            ->where('id', $callId)
            ->where('conversation_id', $conversationId)
            ->where('status', 'ringing')
            ->update([
                'status' => 'declined',
                'ended_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function end(string $callId, int $conversationId): void
    {
        DB::table('live_chat_calls')
            ->where('id', $callId)
            ->where('conversation_id', $conversationId)
            ->whereIn('status', ['ringing', 'accepted'])
            ->update([
                'status' => 'ended',
                'ended_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * A second, video-only WebRTC peer connection is negotiated while the
     * existing audio peer remains connected. Neither party can remotely
     * activate the other party's camera without its explicit approval.
     */
    public function videoUpgrade(
        string $callId,
        int $conversationId,
        string $actor,
        string $action,
        ?array $description = null
    ): stdClass {
        return DB::transaction(function () use (
            $callId, $conversationId, $actor, $action, $description
        ): stdClass {
            $call = DB::table('live_chat_calls')
                ->where('id', $callId)
                ->where('conversation_id', $conversationId)
                ->lockForUpdate()
                ->first();

            if (! $call || $call->status !== 'accepted') {
                throw new RuntimeException('Er is geen verbonden gesprek om naar video over te schakelen.');
            }

            if (! in_array($actor, ['visitor', 'admin'], true)) {
                throw new RuntimeException('Ongeldige deelnemer.');
            }

            $status = (string) ($call->video_upgrade_status ?? '');
            $initiator = (string) ($call->video_upgrade_requested_by ?? '');
            $update = ['updated_at' => now()];

            if ($action === 'request') {
                if ($call->mode !== 'audio' || ($status !== '' && $status !== 'declined')) {
                    throw new RuntimeException('Een video-omschakeling is al bezig of voltooid.');
                }
                if (($description['type'] ?? null) !== 'offer' || ! is_string($description['sdp'] ?? null)) {
                    throw new RuntimeException('Ongeldig videoaanbod.');
                }
                $update += [
                    'video_upgrade_status' => 'requested',
                    'video_upgrade_requested_by' => $actor,
                    'video_upgrade_version' => (int) $call->video_upgrade_version + 1,
                    'video_upgrade_offer_json' => json_encode($description, JSON_UNESCAPED_SLASHES),
                    'video_upgrade_answer_json' => null,
                    'video_upgrade_requested_at' => now(),
                ];
            } elseif ($action === 'accept') {
                if ($status !== 'requested' || $initiator === $actor) {
                    throw new RuntimeException('Er is geen videoverzoek van de andere deelnemer.');
                }
                if (($description['type'] ?? null) !== 'answer' || ! is_string($description['sdp'] ?? null)) {
                    throw new RuntimeException('Ongeldig videoantwoord.');
                }
                if ($call->video_upgrade_requested_at
                    && \Illuminate\Support\Carbon::parse($call->video_upgrade_requested_at)
                        ->lt(now()->subSeconds(90))) {
                    throw new RuntimeException('Het videoverzoek is verlopen.');
                }
                $update += [
                    'video_upgrade_status' => 'accepted',
                    'video_upgrade_answer_json' => json_encode($description, JSON_UNESCAPED_SLASHES),
                    'mode' => 'video',
                ];
            } elseif ($action === 'decline') {
                if (! in_array($status, ['requested', 'accepted'], true)) {
                    throw new RuntimeException('Er is geen videoverzoek om af te wijzen.');
                }
                $update += [
                    'video_upgrade_status' => 'declined',
                    'mode' => 'audio',
                ];
            } else {
                throw new RuntimeException('Ongeldige videoactie.');
            }

            DB::table('live_chat_calls')
                ->where('id', $callId)
                ->where('conversation_id', $conversationId)
                ->update($update);

            return DB::table('live_chat_calls')->where('id', $callId)->first();
        });
    }

    private function expireUnansweredVideoUpgrades(int $conversationId): void
    {
        DB::table('live_chat_calls')
            ->where('conversation_id', $conversationId)
            ->where('status', 'accepted')
            ->where('video_upgrade_status', 'requested')
            ->where('video_upgrade_requested_at', '<', now()->subSeconds(90))
            ->update([
                'video_upgrade_status' => 'declined',
                'updated_at' => now(),
            ]);
    }

    public function payload(?stdClass $call): ?array
    {
        if (! $call) {
            return null;
        }

        return [
            'id' => (string) $call->id,
            'conversation_id' => (int) $call->conversation_id,
            'initiated_by' => (string) $call->initiated_by,
            'mode' => (string) $call->mode,
            'status' => (string) $call->status,
            'video_upgrade' => (int) ($call->video_upgrade_version ?? 0) > 0 ? [
                'version' => (int) $call->video_upgrade_version,
                'status' => (string) $call->video_upgrade_status,
                'requested_by' => (string) $call->video_upgrade_requested_by,
                'offer' => $this->decodeDescription($call->video_upgrade_offer_json ?? null),
                'answer' => $this->decodeDescription($call->video_upgrade_answer_json ?? null),
            ] : null,
            'offer' => $this->decodeDescription($call->offer_json ?? null),
            'answer' => $this->decodeDescription($call->answer_json ?? null),
            'caller_name' => isset($call->caller_name) ? (string) $call->caller_name : null,
            'created_at' => $call->created_at ?? null,
            'answered_at' => $call->answered_at ?? null,
        ];
    }

    private function decodeDescription(?string $json): ?array
    {
        if (! $json) {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function expireStale(int $conversationId): void
    {
        DB::table('live_chat_calls')
            ->where('conversation_id', $conversationId)
            ->where('status', 'ringing')
            ->where('created_at', '<', now()->subSeconds((int) config('live_chat.calls.ring_timeout_seconds', 45)))
            ->update([
                'status' => 'missed',
                'ended_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
