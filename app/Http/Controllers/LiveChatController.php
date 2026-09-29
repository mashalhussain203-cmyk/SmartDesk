<?php

namespace App\Http\Controllers;

use App\Services\LiveChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LiveChatController extends Controller
{
    public function show(
        Request $request,
        LiveChatService $chat
    ): JsonResponse {
        $data = $request->validate([
            'after' => ['sometimes', 'integer', 'min:0'],
        ]);

        return response()
            ->json(
                $chat->payload(
                    $chat->visitorConversation($request),
                    (int) ($data['after'] ?? 0)
                ) + [
                    'identity' => hash(
                        'sha256',
                        $chat->ownerKey($request)
                    ),
                ]
            )
            ->header('Cache-Control', 'no-store');
    }

    public function store(
        Request $request,
        LiveChatService $chat
    ): JsonResponse {
        $type = strtolower(
            trim((string) $request->input('type', 'text'))
        );

        $request->merge(['type' => $type]);

        $rules = [
            'type' => [
                'required',
                Rule::in(['text', 'file', 'voice']),
            ],
            'client_id' => ['required', 'uuid'],
            'body' => [
                $type === 'text' ? 'required' : 'nullable',
                'string',
                'max:4000',
            ],
        ];

        if ($type === 'file') {
            $rules['attachment'] = [
                'required',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,webp,gif,pdf,txt,doc,docx,xls,xlsx',
            ];
        }

        if ($type === 'voice') {
            $rules['attachment'] = [
                'required',
                'file',
                'max:15360',
                'mimetypes:audio/webm,audio/ogg,audio/opus,audio/mpeg,audio/mp3,audio/mp4,audio/x-m4a,audio/m4a,audio/wav,audio/x-wav,video/webm,application/ogg,application/octet-stream',
            ];
        }

        $data = $request->validate($rules, [
            'attachment.required' => $type === 'voice'
                ? 'Er is geen spraakopname ontvangen.'
                : 'Er is geen bestand ontvangen.',
            'attachment.file' => 'Het ontvangen bestand is ongeldig.',
            'attachment.max' => $type === 'voice'
                ? 'Het spraakbericht is te groot. Maximaal 15 MB toegestaan.'
                : 'Het bestand is te groot. Maximaal 20 MB toegestaan.',
            'attachment.mimetypes' => 'Dit bestandsformaat wordt niet ondersteund.',
        ]);

        $attachment = $request->file('attachment');

        if ($type === 'voice' && ! $attachment) {
            return response()->json([
                'message' => 'Het spraakbericht kon niet worden ontvangen.',
                'errors' => [
                    'attachment' => [
                        'Er is geen geldige spraakopname ontvangen.',
                    ],
                ],
            ], 422);
        }

        $conversation = $chat->sendVisitor(
            $request,
            $data,
            $attachment
        );

        // Zodra het bericht is verstuurd is de bezoeker niet meer "aan het typen".
        $chat->setVisitorTyping($request, false);

        return response()
            ->json([
                'conversation' => [
                    'id' => $conversation->id,
                    'status' => $conversation->status,
                ],
            ])
            ->header('Cache-Control', 'no-store');
    }

    public function typing(
        Request $request,
        LiveChatService $chat
    ): JsonResponse {
        $data = $request->validate([
            'typing' => ['required', 'boolean'],
        ]);

        $conversation = $chat->setVisitorTyping(
            $request,
            (bool) $data['typing']
        );

        return response()
            ->json([
                'ok' => true,
                'conversation' => $conversation
                    ? [
                        'id' => (int) $conversation->id,
                        'status' => $conversation->status,
                    ]
                    : null,
            ])
            ->header('Cache-Control', 'no-store');
    }

    public function destroy(
        Request $request,
        LiveChatService $chat,
        int $message
    ): JsonResponse {
        $chat->deleteVisitorMessage($request, $message);

        return response()->json(['ok' => true]);
    }

    public function reopen(
        Request $request,
        LiveChatService $chat
    ): JsonResponse {
        $conversation = $chat->visitorConversation($request);

        abort_unless($conversation, 404);

        DB::table('live_chat_conversations')
            ->where('id', $conversation->id)
            ->where('status', 'closed')
            ->update([
                'status' => 'waiting',
                'updated_at' => now(),
            ]);

        $chat->clearTyping((int) $conversation->id);

        return response()
            ->json(['ok' => true])
            ->header('Cache-Control', 'no-store');
    }
}
