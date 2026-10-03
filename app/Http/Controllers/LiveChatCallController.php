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
                'offer_json' => json_encode($this->encodeDescription($offer, 'offer'), JSON_UNESCAPED_SLASHES),
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

        return DB::table('live_chat_calls as c')
            ->join('live_chat_conversations as lc', 'lc.id', '=', 'c.conversation_id')
            ->leftJoin('users as u', 'u.id', '=', 'lc.user_id')
            ->where('c.status', 'ringing')
            ->where('c.initiated_by', 'visitor')
            ->orderByDesc('c.created_at')
            ->select(
                'c.*',
                DB::raw("COALESCE(NULLIF(u.name, ''), CONCAT('Gast #', lc.id)) as caller_name")
            )
            ->first();
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
                'answer_json' => json_encode($this->encodeDescription($answer, 'answer'), JSON_UNESCAPED_SLASHES),
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

    private function encodeDescription(array $description, string $expectedType): array
    {
        $type = (string) ($description['type'] ?? $expectedType);
        $sdp = (string) ($description['sdp'] ?? '');

        // Store SDP as base64 so CR/LF sequences can never be altered by JSON,
        // a database driver, logging middleware, or response serialization.
        return [
            'type' => $type,
            'sdp_b64' => base64_encode($sdp),
        ];
    }

    private function decodeDescription(?string $json): ?array
    {
        if (! $json) {
            return null;
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return null;
        }

        $type = (string) ($decoded['type'] ?? '');
        $sdp = '';

        if (isset($decoded['sdp_b64']) && is_string($decoded['sdp_b64'])) {
            $raw = base64_decode($decoded['sdp_b64'], true);
            if ($raw !== false) {
                $sdp = $raw;
            }
        } elseif (isset($decoded['sdp']) && is_string($decoded['sdp'])) {
            // Backwards compatibility for calls created before the base64 fix.
            $sdp = $decoded['sdp'];
        }

        // Repair old records that may contain literal escaped newline sequences.
        $sdp = str_replace(["\\r\\n", "\\n", "\\r"], ["\r\n", "\n", "\r"], $sdp);
        $sdp = preg_replace('/^\xEF\xBB\xBF/', '', $sdp) ?? $sdp;
        $sdp = str_replace("\0", '', $sdp);

        if ($type === '' || $sdp === '') {
            return null;
        }

        return [
            'type' => $type,
            'sdp' => $sdp,
        ];
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
