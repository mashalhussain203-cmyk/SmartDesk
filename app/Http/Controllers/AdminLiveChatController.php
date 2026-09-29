<?php

namespace App\Http\Controllers;

use App\Services\LiveChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminLiveChatController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            (bool) $request->user()?->is_admin,
            403
        );
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.live-chat');
    }

    public function conversations(
        Request $request,
        LiveChatService $chat
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'filter' => [
                'sometimes',
                'in:active,closed',
            ],
        ]);

        $query = DB::table(
            'live_chat_conversations as c'
        )
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'c.user_id'
            )
            ->select(
                'c.id',
                'c.user_id',
                'c.status',
                'c.last_message_at',
                'u.name',
                'u.email'
            )
            ->selectSub(
                function ($query): void {
                    $query
                        ->from(
                            'live_chat_messages as m'
                        )
                        ->selectRaw(
                            'COUNT(*)'
                        )
                        ->whereColumn(
                            'm.conversation_id',
                            'c.id'
                        )
                        ->where(
                            'm.sender',
                            'visitor'
                        )
                        ->whereColumn(
                            'm.id',
                            '>',
                            'c.admin_last_read_id'
                        );
                },
                'unread'
            );

        if (
            ($data['filter'] ?? 'active')
            === 'closed'
        ) {
            $query->where(
                'c.status',
                'closed'
            );
        } else {
            $query->where(
                'c.status',
                '!=',
                'closed'
            );
        }

        $page = $query
            ->orderByDesc(
                'c.last_message_at'
            )
            ->orderByDesc(
                'c.id'
            )
            ->paginate(30);

        $items = $page
            ->getCollection()
            ->map(
                function (object $conversation) use ($chat): array {
                    $userId = $conversation->user_id
                        ? (int) $conversation->user_id
                        : null;

                    $identity = $chat->userIdentity(
                        $userId
                    );

                    return [
                        'id' => (int) $conversation->id,

                        'status' =>
                            $conversation->status,

                        'name' => $userId
                            ? $conversation->name
                            : 'Gast #'.$conversation->id,

                        'email' => $userId
                            ? $conversation->email
                            : null,

                        'avatar' =>
                            $identity['avatar'],

                        'kind' => $userId
                            ? 'account'
                            : 'guest',

                        'unread' =>
                            (int) $conversation->unread,

                        'last_message_at' =>
                            $conversation->last_message_at,
                    ];
                }
            )
            ->all();

        return response()
            ->json([
                'items' => $items,

                'page' =>
                    $page->currentPage(),

                'last_page' =>
                    $page->lastPage(),
            ])
            ->header(
                'Cache-Control',
                'no-store'
            );
    }

    public function show(
        Request $request,
        LiveChatService $chat,
        int $conversation
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'after' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ]);

        $record = DB::table(
            'live_chat_conversations'
        )
            ->where(
                'id',
                $conversation
            )
            ->first();

        abort_unless(
            $record,
            404
        );

        $payload = $chat->payload(
            $record,
            (int) ($data['after'] ?? 0)
        );

        $lastId = collect(
            $payload['messages']
        )->max('id');

        if ($lastId) {
            DB::table(
                'live_chat_conversations'
            )
                ->where(
                    'id',
                    $conversation
                )
                ->where(
                    'admin_last_read_id',
                    '<',
                    $lastId
                )
                ->update([
                    'admin_last_read_id' =>
                        $lastId,
                ]);
        }

        return response()
            ->json(
                $payload
            )
            ->header(
                'Cache-Control',
                'no-store'
            );
    }

    public function store(
        Request $request,
        LiveChatService $chat,
        int $conversation
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $type = strtolower(
            trim(
                (string) $request->input(
                    'type',
                    'text'
                )
            )
        );

        $request->merge([
            'type' => $type,
        ]);

        $rules = [
            'type' => [
                'required',
                Rule::in([
                    'text',
                    'file',
                    'voice',
                ]),
            ],

            'client_id' => [
                'required',
                'uuid',
            ],

            'body' => [
                $type === 'text'
                    ? 'required'
                    : 'nullable',

                'string',
                'max:4000',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Gewone bestanden
        |--------------------------------------------------------------------------
        */
        if ($type === 'file') {
            $rules['attachment'] = [
                'required',
                'file',
                'max:20480',

                'mimes:'.
                    'jpg,'.
                    'jpeg,'.
                    'png,'.
                    'webp,'.
                    'gif,'.
                    'pdf,'.
                    'txt,'.
                    'doc,'.
                    'docx,'.
                    'xls,'.
                    'xlsx',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Spraakberichten
        |--------------------------------------------------------------------------
        |
        | Chrome en Edge gebruiken meestal WebM + Opus.
        | Firefox gebruikt vaak OGG/Opus.
        | Safari gebruikt vaker MP4/M4A.
        |
        | Sommige systemen detecteren een audio-opname als video/webm,
        | daarom wordt die MIME ook toegestaan.
        |
        */
        if ($type === 'voice') {
            $rules['attachment'] = [
                'required',
                'file',
                'max:15360',

                'mimetypes:'.
                    'audio/webm,'.
                    'audio/ogg,'.
                    'audio/opus,'.
                    'audio/mpeg,'.
                    'audio/mp3,'.
                    'audio/mp4,'.
                    'audio/m4a,'.
                    'audio/x-m4a,'.
                    'audio/wav,'.
                    'audio/x-wav,'.
                    'video/webm,'.
                    'application/ogg,'.
                    'application/octet-stream',
            ];
        }

        $data = $request->validate(
            $rules,
            [
                'attachment.required' =>
                    $type === 'voice'
                        ? 'Er is geen spraakopname ontvangen.'
                        : 'Er is geen bestand ontvangen.',

                'attachment.file' =>
                    'Het ontvangen bestand is ongeldig.',

                'attachment.max' =>
                    $type === 'voice'
                        ? 'Het spraakbericht is te groot. Maximaal 15 MB toegestaan.'
                        : 'Het bestand is te groot. Maximaal 20 MB toegestaan.',

                'attachment.mimetypes' =>
                    'Dit spraakberichtbestand wordt niet ondersteund.',
            ]
        );

        $attachment = $request->file(
            'attachment'
        );

        if (
            $type === 'voice'
            && ! $attachment
        ) {
            return response()->json(
                [
                    'message' =>
                        'Het spraakbericht kon niet worden ontvangen.',

                    'errors' => [
                        'attachment' => [
                            'Er is geen geldige spraakopname ontvangen.',
                        ],
                    ],
                ],
                422
            );
        }

        $chat->sendAdmin(
            $conversation,
            (int) $request->user()->id,
            $data,
            $attachment
        );

        return response()
            ->json([
                'ok' => true,
            ])
            ->header(
                'Cache-Control',
                'no-store'
            );
    }

    public function destroy(
        Request $request,
        LiveChatService $chat,
        int $conversation,
        int $message
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $chat->deleteAdminMessage(
            $conversation,
            $message
        );

        return response()->json([
            'ok' => true,
        ]);
    }

    public function update(
        Request $request,
        int $conversation
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'open',
                    'closed',
                ]),
            ],
        ]);

        DB::transaction(
            function () use (
                $conversation,
                $data
            ): void {
                $record = DB::table(
                    'live_chat_conversations'
                )
                    ->where(
                        'id',
                        $conversation
                    )
                    ->lockForUpdate()
                    ->first();

                abort_unless(
                    $record,
                    404
                );

                DB::table(
                    'live_chat_conversations'
                )
                    ->where(
                        'id',
                        $conversation
                    )
                    ->update([
                        'status' =>
                            $data['status'],

                        'updated_at' =>
                            now(),
                    ]);
            }
        );

        return response()->json([
            'ok' => true,
        ]);
    }

    public function presence(
        Request $request
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'online' => [
                'required',
                'boolean',
            ],
        ]);

        if ($data['online']) {
            DB::table(
                'live_chat_agents'
            )->updateOrInsert(
                [
                    'user_id' =>
                        $request->user()->id,
                ],
                [
                    'last_seen_at' =>
                        now(),
                ]
            );
        } else {
            DB::table(
                'live_chat_agents'
            )
                ->where(
                    'user_id',
                    $request->user()->id
                )
                ->delete();
        }

        return response()->json([
            'ok' => true,
        ]);
    }
}
