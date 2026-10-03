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

            // Een oude/mislukte call mag een nieuwe call nooit blokkeren.
            // De browser verhindert zelf dubbel starten tijdens een echte actieve sessie.
            DB::table('live_chat_calls')
                ->where('conversation_id', $conversationId)
                ->whereIn('status', ['ringing', 'accepted'])
                ->update([
                    'status' => 'failed',
                    'ended_at' => now(),
                    'updated_at' => now(),
                ]);

            $id = (string) Str::uuid();
            $now = now();

            DB::table('live_chat_calls')->insert([
                'id' => $id,
                'conversation_id' => $conversationId,
                'initiated_by' => $initiatedBy,
                'admin_user_id' => $adminUserId,
                'mode' => $mode,
                'status' => 'ringing',
                'offer_json' => $this->encodeDescription($offer, 'offer'),
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
                'answer_json' => $this->encodeDescription($answer, 'answer'),
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

    public function fail(string $callId, int $conversationId): void
    {
        DB::table('live_chat_calls')
            ->where('id', $callId)
            ->where('conversation_id', $conversationId)
            ->whereIn('status', ['ringing', 'accepted'])
            ->update([
                'status' => 'failed',
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
            // SDP gaat als base64 over de API zodat JSON/Safari nooit regels kan beschadigen.
            'offer' => $this->descriptionForTransport($call->offer_json ?? null),
            'answer' => $this->descriptionForTransport($call->answer_json ?? null),
            'caller_name' => isset($call->caller_name) ? (string) $call->caller_name : null,
            'created_at' => $call->created_at ?? null,
            'answered_at' => $call->answered_at ?? null,
        ];
    }

    private function encodeDescription(array $description, string $expectedType): string
    {
        $type = (string) ($description['type'] ?? $expectedType);
        $sdp = '';

        if (isset($description['sdp_b64']) && is_string($description['sdp_b64'])) {
            $decoded = base64_decode($description['sdp_b64'], true);
            if ($decoded !== false) {
                $sdp = $decoded;
            }
        } elseif (isset($description['sdp']) && is_string($description['sdp'])) {
            // Backward compatibility met oudere JS.
            $sdp = $description['sdp'];
        }

        if ($type !== $expectedType || $sdp === '' || ! str_starts_with($sdp, 'v=0')) {
            throw new RuntimeException('Ongeldige WebRTC session description.');
        }

        return json_encode([
            '_encoding' => 'base64-sdp-v2',
            'type' => $type,
            'sdp' => base64_encode($sdp),
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function decodeDescription(?string $json): ?array
    {
        if (! is_string($json) || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return null;
        }

        if (in_array(($decoded['_encoding'] ?? null), ['base64-sdp-v1', 'base64-sdp-v2'], true)) {
            $sdp = base64_decode((string) ($decoded['sdp'] ?? ''), true);
            if ($sdp === false || $sdp === '') {
                return null;
            }
            return [
                'type' => (string) ($decoded['type'] ?? ''),
                'sdp' => $sdp,
            ];
        }

        if (isset($decoded['type'], $decoded['sdp']) && is_string($decoded['sdp'])) {
            return [
                'type' => (string) $decoded['type'],
                'sdp' => $decoded['sdp'],
            ];
        }

        return null;
    }

    private function descriptionForTransport(?string $json): ?array
    {
        $decoded = $this->decodeDescription($json);
        if (! $decoded) {
            return null;
        }

        return [
            'type' => $decoded['type'],
            'sdp_b64' => base64_encode($decoded['sdp']),
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
