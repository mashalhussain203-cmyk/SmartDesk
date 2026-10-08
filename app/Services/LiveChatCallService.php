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
