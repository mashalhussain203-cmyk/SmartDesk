<?php

namespace App\Http\Controllers;

use App\Services\LiveChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LiveChatController extends Controller
{
    public function show(Request $request, LiveChatService $chat): JsonResponse
    {
        $data = $request->validate(['after' => ['sometimes', 'integer', 'min:0']]);

        return response()->json($chat->payload($chat->visitorConversation($request), (int) ($data['after'] ?? 0)) + [
            'identity' => hash('sha256', $chat->ownerKey($request)),
        ])->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, LiveChatService $chat): JsonResponse
    {
        $type = (string) $request->input('type', 'text');
        $request->merge(['type' => $type]);

        $rules = [
            'type' => ['required', Rule::in(['text', 'file', 'voice'])],
            'client_id' => ['required', 'uuid'],
            'body' => [$type === 'text' ? 'required' : 'nullable', 'string', 'max:4000'],
        ];

        if ($type === 'file') {
            $rules['attachment'] = ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif,pdf,txt,doc,docx,xls,xlsx'];
        } elseif ($type === 'voice') {
            $rules['attachment'] = ['required', 'file', 'max:15360', 'mimetypes:audio/webm,audio/ogg,audio/mpeg,audio/mp4,audio/wav,video/webm,application/octet-stream'];
        }

        $data = $request->validate($rules);
        $conversation = $chat->sendVisitor($request, $data, $request->file('attachment'));

        return response()->json(['conversation' => ['id' => $conversation->id, 'status' => $conversation->status]])
            ->header('Cache-Control', 'no-store');
    }

    public function destroy(Request $request, LiveChatService $chat, int $message): JsonResponse
    {
        $chat->deleteVisitorMessage($request, $message);

        return response()->json(['ok' => true]);
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
