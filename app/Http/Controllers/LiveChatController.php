<?php

namespace App\Http\Controllers;

use App\Services\LiveChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveChatController extends Controller
{
    public function show(Request $request, LiveChatService $chat): JsonResponse
    {
        $data = $request->validate(['after' => ['sometimes', 'integer', 'min:0']]);

        return response()->json($chat->payload($chat->visitorConversation($request), (int) ($data['after'] ?? 0)) + [
            'identity' => hash('sha256', $chat->ownerKey($request)),
        ])
            ->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, LiveChatService $chat): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
            'client_id' => ['required', 'uuid'],
        ]);
        $conversation = $chat->sendVisitor($request, $data);

        return response()->json(['conversation' => ['id' => $conversation->id, 'status' => $conversation->status]])
            ->header('Cache-Control', 'no-store');
    }

    public function reopen(Request $request, LiveChatService $chat): JsonResponse
    {
        $conversation = $chat->visitorConversation($request);
        abort_unless($conversation, 404);
        DB::table('live_chat_conversations')->where('id', $conversation->id)->where('status', 'closed')
            ->update(['status' => 'waiting', 'updated_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
