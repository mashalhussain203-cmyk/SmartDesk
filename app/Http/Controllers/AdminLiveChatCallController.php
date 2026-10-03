<?php

namespace App\Http\Controllers;

use App\Services\LiveChatCallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class AdminLiveChatCallController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless((bool) $request->user()?->is_admin, 403);
    }

    private function conversation(int $conversation): object
    {
        $record = DB::table('live_chat_conversations')->where('id', $conversation)->first();
        abort_unless($record, 404);
        return $record;
    }

    public function incoming(Request $request, LiveChatCallService $calls): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'call' => $calls->payload($calls->incomingForAdmin()),
        ])->header('Cache-Control', 'no-store');
    }

    public function current(
        Request $request,
        LiveChatCallService $calls,
        int $conversation
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $this->conversation($conversation);

        return response()->json([
            'call' => $calls->payload($calls->current($conversation)),
        ])->header('Cache-Control', 'no-store');
    }

    public function start(
        Request $request,
        LiveChatCallService $calls,
        int $conversation
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $record = $this->conversation($conversation);

        if (($record->delivery_channel ?? 'live') === 'email') {
            return response()->json([
                'message' => 'Dit gesprek loopt momenteel via e-mail.',
            ], 409);
        }

        $data = $request->validate([
            'mode' => ['required', Rule::in(['audio', 'video'])],
            'offer.type' => ['required', 'string', 'in:offer'],
            'offer.sdp_b64' => ['nullable', 'string', 'max:700000'],
            'offer.sdp' => ['nullable', 'string', 'max:500000'],
        ]);

        if (empty($data['offer']['sdp_b64']) && empty($data['offer']['sdp'])) {
            return response()->json(['message' => 'SDP offer ontbreekt.'], 422);
        }

        try {
            $call = $calls->start(
                $conversation,
                'admin',
                (string) $data['mode'],
                $data['offer'],
                (int) $request->user()->id
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
        LiveChatCallService $calls,
        int $conversation,
        string $call
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $this->conversation($conversation);

        $data = $request->validate([
            'answer.type' => ['required', 'string', 'in:answer'],
            'answer.sdp_b64' => ['nullable', 'string', 'max:700000'],
            'answer.sdp' => ['nullable', 'string', 'max:500000'],
        ]);

        if (empty($data['answer']['sdp_b64']) && empty($data['answer']['sdp'])) {
            return response()->json(['message' => 'SDP answer ontbreekt.'], 422);
        }

        try {
            $record = $calls->answer($call, $conversation, $data['answer']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['call' => $calls->payload($record)])
            ->header('Cache-Control', 'no-store');
    }

    public function decline(
        Request $request,
        LiveChatCallService $calls,
        int $conversation,
        string $call
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $this->conversation($conversation);
        $calls->decline($call, $conversation);

        return response()->json(['ok' => true]);
    }

    public function end(
        Request $request,
        LiveChatCallService $calls,
        int $conversation,
        string $call
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $this->conversation($conversation);
        $calls->end($call, $conversation);

        return response()->json(['ok' => true]);
    }
}
