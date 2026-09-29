<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class LiveChatService
{
    public function ownerKey(Request $request): string
    {
        if ($request->user()) {
            return 'user:'.$request->user()->getAuthIdentifier();
        }
        $token = $request->session()->get('live_chat.guest_token');
        if (! is_string($token) || strlen($token) !== 64) {
            $token = Str::random(64);
            $request->session()->put('live_chat.guest_token', $token);
        }

        return 'guest:'.hash('sha256', $token);
    }

    public function visitorConversation(Request $request): ?stdClass
    {
        return DB::table('live_chat_conversations')->where('owner_key', $this->ownerKey($request))->first();
    }

    public function online(): bool
    {
        return DB::table('live_chat_agents')->join('users', 'users.id', '=', 'live_chat_agents.user_id')
            ->where('users.is_admin', true)->where('last_seen_at', '>', now()->subSeconds(60))->exists();
    }

    /** @return array<string, mixed> */
    public function payload(?stdClass $conversation, int $after = 0): array
    {
        $messages = collect();
        if ($conversation) {
            $query = DB::table('live_chat_messages')->where('conversation_id', $conversation->id);
            $messages = $after > 0
                ? $query->where('id', '>', $after)->orderBy('id')->limit(100)->get()
                : $query->orderByDesc('id')->limit(100)->get()->reverse()->values();
        }

        return [
            'conversation' => $conversation ? ['id' => $conversation->id, 'status' => $conversation->status] : null,
            'online' => $this->online(),
            'messages' => $messages->map(fn (stdClass $message): array => [
                'id' => $message->id,
                'client_id' => $message->client_id,
                'sender' => $message->sender,
                'body' => $message->body,
                'created_at' => $message->created_at,
            ])->all(),
        ];
    }

    /** @param array{body: string, client_id: string} $data */
    public function sendVisitor(Request $request, array $data): stdClass
    {
        $owner = $this->ownerKey($request);

        return DB::transaction(function () use ($request, $data, $owner): stdClass {
            DB::table('live_chat_conversations')->insertOrIgnore([
                'owner_key' => $owner, 'user_id' => $request->user()?->getAuthIdentifier(),
                'status' => 'waiting', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $conversation = DB::table('live_chat_conversations')->where('owner_key', $owner)->lockForUpdate()->first();
            abort_unless($conversation, 503);
            $existing = DB::table('live_chat_messages')->where('conversation_id', $conversation->id)
                ->where('client_id', $data['client_id'])->first();
            if ($existing) {
                abort_unless($existing->sender === 'visitor' && $existing->body === $data['body'], 409);

                return $conversation;
            }
            abort_if($conversation->status === 'closed', 409, 'Dit gesprek is gesloten. Start het gesprek opnieuw.');
            $this->insertMessage($conversation->id, 'visitor', $data);

            return DB::table('live_chat_conversations')->where('id', $conversation->id)->first();
        });
    }

    /** @param array{body: string, client_id: string} $data */
    public function sendAdmin(int $id, array $data): stdClass
    {
        return DB::transaction(function () use ($id, $data): stdClass {
            $conversation = DB::table('live_chat_conversations')->where('id', $id)->lockForUpdate()->first();
            abort_unless($conversation, 404);
            $existing = DB::table('live_chat_messages')->where('conversation_id', $id)->where('client_id', $data['client_id'])->first();
            if ($existing) {
                abort_unless($existing->sender === 'admin' && $existing->body === $data['body'], 409);

                return $conversation;
            }
            abort_if($conversation->status === 'closed', 409, 'Open het gesprek voordat je antwoordt.');
            $this->insertMessage($id, 'admin', $data);
            DB::table('live_chat_conversations')->where('id', $id)->update(['status' => 'open']);

            return DB::table('live_chat_conversations')->where('id', $id)->first();
        });
    }

    /** @param array{body: string, client_id: string} $data */
    private function insertMessage(int $conversationId, string $sender, array $data): void
    {
        DB::table('live_chat_messages')->insert([
            'conversation_id' => $conversationId, 'client_id' => $data['client_id'],
            'sender' => $sender, 'body' => $data['body'], 'created_at' => now(),
        ]);
        DB::table('live_chat_conversations')->where('id', $conversationId)->update([
            'last_message_at' => now(), 'updated_at' => now(),
        ]);
    }
}
