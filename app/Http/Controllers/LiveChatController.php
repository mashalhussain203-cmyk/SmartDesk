<?php

namespace App\Http\Controllers;

use App\Services\LiveChatChunkUploadService;
use App\Services\LiveChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $payload = $chat->payload(
            $chat->visitorConversation($request),
            (int) ($data['after'] ?? 0)
        ) + [
            'identity' => hash(
                'sha256',
                $chat->ownerKey($request)
            ),
        ];

        $payload['messages'] = collect($payload['messages'] ?? [])
            ->map(function (array $message): array {
                if (! empty($message['attachment_url'])) {
                    $message['attachment_url'] = route(
                        'live-chat.attachment',
                        ['message' => $message['id']]
                    );
                    $message['attachment_download_url'] = route(
                        'live-chat.attachment',
                        ['message' => $message['id'], 'download' => 1]
                    );
                }

                return $message;
            })
            ->all();

        return response()
            ->json($payload)
            ->header('Cache-Control', 'no-store');
    }

    public function store(
        Request $request,
        LiveChatService $chat
    ): JsonResponse {
        $existingConversation = $chat->visitorConversation(
            $request
        );

        if (
            $existingConversation
            && (
                $existingConversation->delivery_channel
                ?? 'live'
            ) === 'email'
        ) {
            return response()->json(
                [
                    'message' =>
                        'Dit gesprek gaat verder via e-mail. Antwoord op de e-mail van Mashal Support om verder te chatten.',
                ],
                409
            );
        }

        $type = strtolower(
            trim((string) $request->input('type', 'text'))
        );

        $request->merge(['type' => $type]);

        $rules = [
            'type' => [
                'required',
                Rule::in(['text', 'file', 'voice', 'video']),
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

        if ($type === 'video') {
            $rules['attachment'] = [
                'required',
                'file',
                'max:51200',
                'mimes:mp4,webm,mov,m4v,avi,mkv,mpeg,mpg,3gp,3g2,ogv,ts,mts,m2ts,flv,wmv',
            ];
        }

        $data = $request->validate($rules, [
            'attachment.required' => match ($type) {
                'voice' => 'Er is geen spraakopname ontvangen.',
                'video' => 'Er is geen video ontvangen.',
                default => 'Er is geen bestand ontvangen.',
            },
            'attachment.file' => 'Het ontvangen bestand is ongeldig.',
            'attachment.max' => match ($type) {
                'voice' => 'Het spraakbericht is te groot. Maximaal 15 MB toegestaan.',
                'video' => 'De video is te groot. Maximaal 50 MB toegestaan.',
                default => 'Het bestand is te groot. Maximaal 20 MB toegestaan.',
            },
            'attachment.mimetypes' => $type === 'video'
                ? 'Dit videoformaat wordt niet ondersteund.'
                : 'Dit bestandsformaat wordt niet ondersteund.',
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
                    'delivery_channel' =>
                        $conversation->delivery_channel
                        ?? 'live',
                ],
            ])
            ->header('Cache-Control', 'no-store');
    }


    public function uploadStart(
        Request $request,
        LiveChatService $chat,
        LiveChatChunkUploadService $uploads
    ): JsonResponse {
        $conversation = $chat->visitorConversation($request);
        if ($conversation && (($conversation->delivery_channel ?? 'live') === 'email')) {
            return response()->json(['message' => 'Dit gesprek gaat verder via e-mail.'], 409);
        }
        if ($conversation && $conversation->status === 'closed') {
            return response()->json(['message' => 'Dit gesprek is gesloten. Start het gesprek opnieuw.'], 409);
        }

        $data = $request->validate([
            'client_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'mime' => ['nullable', 'string', 'max:100'],
            'size' => ['required', 'integer', 'min:1', 'max:1073741824'],
        ]);

        try {
            return response()->json($uploads->start('visitor:'.$chat->ownerKey($request), $data));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function uploadStatus(
        Request $request,
        LiveChatService $chat,
        LiveChatChunkUploadService $uploads,
        string $upload
    ): JsonResponse {
        try {
            return response()->json($uploads->status('visitor:'.$chat->ownerKey($request), $upload));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function uploadChunk(
        Request $request,
        LiveChatService $chat,
        LiveChatChunkUploadService $uploads,
        string $upload,
        int $index
    ): JsonResponse {
        $request->validate([
            'chunk' => ['required', 'file', 'max:5120'],
        ]);

        try {
            return response()->json(
                $uploads->storeChunk(
                    'visitor:'.$chat->ownerKey($request),
                    $upload,
                    $index,
                    $request->file('chunk')
                )
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function uploadComplete(
        Request $request,
        LiveChatService $chat,
        LiveChatChunkUploadService $uploads,
        string $upload
    ): JsonResponse {
        $conversation = $chat->visitorConversation($request);
        if ($conversation && (($conversation->delivery_channel ?? 'live') === 'email')) {
            return response()->json(['message' => 'Dit gesprek gaat verder via e-mail.'], 409);
        }

        try {
            $attachment = $uploads->complete('visitor:'.$chat->ownerKey($request), $upload);
            $conversation = $chat->sendVisitor(
                $request,
                [
                    'client_id' => $attachment['client_id'],
                    'type' => 'video',
                    'body' => null,
                ],
                null,
                $attachment
            );
            $chat->setVisitorTyping($request, false);

            return response()->json([
                'ok' => true,
                'conversation' => [
                    'id' => $conversation->id,
                    'status' => $conversation->status,
                    'delivery_channel' => $conversation->delivery_channel ?? 'live',
                ],
            ])->header('Cache-Control', 'no-store');
        } catch (\RuntimeException $e) {
            if (isset($attachment['path'])) {
                Storage::disk('public')->delete($attachment['path']);
            }
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            if (isset($attachment['path'])) {
                Storage::disk('public')->delete($attachment['path']);
            }
            throw $e;
        }
    }

    public function uploadCancel(
        Request $request,
        LiveChatService $chat,
        LiveChatChunkUploadService $uploads,
        string $upload
    ): JsonResponse {
        try {
            $uploads->cancel('visitor:'.$chat->ownerKey($request), $upload);
            return response()->json(['ok' => true]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function attachment(
        Request $request,
        LiveChatService $chat,
        int $message
    ) {
        $conversation = $chat->visitorConversation($request);
        abort_unless($conversation, 404);

        $record = DB::table('live_chat_messages')
            ->where('id', $message)
            ->where('conversation_id', $conversation->id)
            ->first();

        abort_unless($record && $record->attachment_path, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($record->attachment_path), 404, 'Bestand niet beschikbaar.');

        $path = $disk->path($record->attachment_path);
        $name = $record->attachment_name ?: basename($record->attachment_path);
        $headers = [
            'Content-Type' => $record->attachment_mime ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
            'Accept-Ranges' => 'bytes',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($request->boolean('download')) {
            return response()->download($path, $name, $headers);
        }

        return response()->file($path, $headers);
    }

    public function emailAttachment(
        Request $request,
        int $message
    ) {
        $record = DB::table('live_chat_messages')
            ->where('id', $message)
            ->first();

        abort_unless($record && $record->attachment_path, 404);

        $disk = Storage::disk('public');
        abort_unless(
            $disk->exists($record->attachment_path),
            404,
            'Bestand niet beschikbaar.'
        );

        $path = $disk->path($record->attachment_path);
        $name = $record->attachment_name
            ?: basename($record->attachment_path);

        $headers = [
            'Content-Type' => $record->attachment_mime
                ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
            'Accept-Ranges' => 'bytes',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($request->boolean('download')) {
            return response()->download(
                $path,
                $name,
                $headers
            );
        }

        return response()->file(
            $path,
            $headers
        );
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
