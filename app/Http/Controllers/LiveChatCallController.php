<?php

namespace App\Http\Controllers;

use App\Services\LiveChatCallService;
use App\Services\LiveChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class LiveChatCallController extends Controller
{
    public function config(LiveChatCallService $calls): JsonResponse
    {
        return response()->json([
            'ice_servers' => $calls->iceServers(),
            'ring_timeout_seconds' => (int) config('live_chat.calls.ring_timeout_seconds', 45),
        ])->header('Cache-Control', 'no-store');
    }

    public function current(
        Request $request,
        LiveChatService $chat,
        LiveChatCallService $calls
    ): JsonResponse {
        $conversation = $chat->visitorConversation($request);

        return response()->json([
            'call' => $conversation
                ? $calls->payload($calls->current((int) $conversation->id))
                : null,
        ])->header('Cache-Control', 'no-store');
    }

    public function start(
        Request $request,
        LiveChatService $chat,
        LiveChatCallService $calls
    ): JsonResponse {
        $conversation = $chat->visitorConversation($request);

        if (! $conversation) {
            return response()->json([
                'message' => 'Stuur eerst een chatbericht voordat je belt.',
            ], 409);
        }

        if (($conversation->delivery_channel ?? 'live') === 'email') {
            return response()->json([
                'message' => 'Dit gesprek loopt momenteel via e-mail.',
            ], 409);
        }

        $data = $request->validate([
            'mode' => ['required', Rule::in(['audio', 'video'])],
            'offer.type' => ['required', 'string', 'in:offer'],
            'offer.sdp' => ['required', 'string', 'max:200000'],
        ]);

        try {
            $call = $calls->start(
                (int) $conversation->id,
                'visitor',
                (string) $data['mode'],
                $data['offer']
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'call' => $calls->payload($call),
        ], 201)->header('Cache-Control', 'no-store');
    }

    public function answer(
        Request $request,
        LiveChatService $chat,
        LiveChatCallService $calls,
        string $call
    ): JsonResponse {
        $conversation = $chat->visitorConversation($request);
        abort_unless($conversation, 404);

        $data = $request->validate([
            'answer.type' => ['required', 'string', 'in:answer'],
            'answer.sdp' => ['required', 'string', 'max:200000'],
        ]);

        try {
            $record = $calls->answer($call, (int) $conversation->id, $data['answer']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['call' => $calls->payload($record)])
            ->header('Cache-Control', 'no-store');
    }

    public function decline(
        Request $request,
        LiveChatService $chat,
        LiveChatCallService $calls,
        string $call
    ): JsonResponse {
        $conversation = $chat->visitorConversation($request);
        abort_unless($conversation, 404);
        $calls->decline($call, (int) $conversation->id);

        return response()->json(['ok' => true]);
    }

    public function end(
        Request $request,
        LiveChatService $chat,
        LiveChatCallService $calls,
        string $call
    ): JsonResponse {
        $conversation = $chat->visitorConversation($request);
        abort_unless($conversation, 404);
        $calls->end($call, (int) $conversation->id);

        return response()->json(['ok' => true]);
    }
}
