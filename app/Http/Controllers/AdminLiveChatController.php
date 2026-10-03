<?php



namespace App\Http\Controllers;



use App\Services\GmailLiveChatInboxService;
use App\Services\LiveChatEmailService;
use App\Services\LiveChatChunkUploadService;
use App\Services\LiveChatService;

use Illuminate\Contracts\View\View;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'priority' => ['sometimes', 'nullable', 'in:low,normal,high,urgent'],
            'assigned_admin_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],

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
                'c.priority',
                'c.labels',
                'c.assigned_admin_id',
                'c.blocked_at',

                'c.last_message_at',

                'c.delivery_channel',

                'c.contact_email',

                'c.email_handoff_at',

                'c.email_subject',

                'c.email_title',

                'c.gmail_thread_id',

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



        if (! empty($data['q'])) {
            $term = trim((string) $data['q']);
            $query->where(function ($sub) use ($term): void {
                $like = '%'.$term.'%';
                $sub->where('u.name', 'like', $like)
                    ->orWhere('u.email', 'like', $like)
                    ->orWhere('c.contact_email', 'like', $like)
                    ->orWhereExists(function ($messageQuery) use ($like): void {
                        $messageQuery->selectRaw('1')
                            ->from('live_chat_messages as sm')
                            ->whereColumn('sm.conversation_id', 'c.id')
                            ->where('sm.body', 'like', $like);
                    });
            });
        }

        if (! empty($data['priority'])) {
            $query->where('c.priority', $data['priority']);
        }

        if (! empty($data['assigned_admin_id'])) {
            $query->where('c.assigned_admin_id', (int) $data['assigned_admin_id']);
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
                        'priority' => $conversation->priority ?? 'normal',
                        'labels' => is_string($conversation->labels ?? null)
                            ? (json_decode($conversation->labels, true) ?: [])
                            : ($conversation->labels ?? []),
                        'assigned_admin_id' => $conversation->assigned_admin_id ?? null,
                        'blocked_at' => $conversation->blocked_at ?? null,

                        'name' => $userId

                            ? $conversation->name

                            : 'Gast #'.$conversation->id,



                        'email' =>

                            $conversation->contact_email

                            ?: (

                                $userId

                                    ? $conversation->email

                                    : null

                            ),



                        'delivery_channel' =>

                            $conversation->delivery_channel

                            ?? 'live',



                        'email_handoff_at' =>

                            $conversation->email_handoff_at

                            ?? null,



                        'email_subject' =>

                            $conversation->email_subject

                            ?? null,



                        'email_title' =>

                            $conversation->email_title

                            ?? 'Mashal Support',



                        'gmail_thread_id' =>

                            $conversation->gmail_thread_id

                            ?? null,



                        'email_subject_locked' =>

                            ! empty($conversation->gmail_thread_id),



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

            'live_chat_conversations as c'

        )

            ->leftJoin(

                'users as u',

                'u.id',

                '=',

                'c.user_id'

            )

            ->where(

                'c.id',

                $conversation

            )

            ->select(

                'c.*',

                'u.name as user_name',

                'u.email as user_email'

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



        $payload['conversation'] = array_merge(

            $payload['conversation'] ?? [],

            [

                'name' => $record->user_id

                    ? ($record->user_name ?: 'Gebruiker')

                    : 'Gast #'.$record->id,

                'email' => $record->contact_email

                    ?: $record->user_email,

                'delivery_channel' =>

                    $record->delivery_channel

                    ?? 'live',

                'email_handoff_at' =>

                    $record->email_handoff_at

                    ?? null,

                'email_subject' =>

                    $record->email_subject

                    ?? null,

                'email_title' =>

                    $record->email_title

                    ?? 'Mashal Support',

                'gmail_thread_id' =>

                    $record->gmail_thread_id

                    ?? null,

                'email_subject_locked' =>

                    ! empty($record->gmail_thread_id),

            ]

        );



        $payload['messages'] = collect($payload['messages'] ?? [])
            ->map(function (array $message) use ($conversation): array {
                if (! empty($message['attachment_url'])) {
                    $message['attachment_url'] = route(
                        'admin.live-chat.attachment',
                        [
                            'conversation' => $conversation,
                            'message' => $message['id'],
                        ]
                    );
                    $message['attachment_download_url'] = route(
                        'admin.live-chat.attachment',
                        [
                            'conversation' => $conversation,
                            'message' => $message['id'],
                            'download' => 1,
                        ]
                    );
                }

                return $message;
            })
            ->all();

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

        LiveChatEmailService $email,

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

                    'video',

                ]),

            ],



            'client_id' => [

                'required',

                'uuid',

            ],
            'parent_message_id' => [
                'nullable',
                'integer',
                'exists:live_chat_messages,id',
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



        if ($type === 'video') {

            $rules['attachment'] = [

                'required',

                'file',

                'max:51200',

                'mimetypes:'.

                    'video/mp4,'.

                    'video/webm,'.

                    'video/quicktime,'.

                    'video/x-m4v',

            ];

        }



        $data = $request->validate(

            $rules,

            [

                'attachment.required' => match ($type) {

                    'voice' => 'Er is geen spraakopname ontvangen.',

                    'video' => 'Er is geen video ontvangen.',

                    default => 'Er is geen bestand ontvangen.',

                },

                'attachment.file' =>

                    'Het ontvangen bestand is ongeldig.',

                'attachment.max' => match ($type) {

                    'voice' => 'Het spraakbericht is te groot. Maximaal 15 MB toegestaan.',

                    'video' => 'De video is te groot. Maximaal 50 MB toegestaan.',

                    default => 'Het bestand is te groot. Maximaal 20 MB toegestaan.',

                },

                'attachment.mimetypes' => match ($type) {

                    'voice' => 'Dit spraakberichtbestand wordt niet ondersteund.',

                    'video' => 'Dit videoformaat wordt niet ondersteund.',

                    default => 'Dit bestandsformaat wordt niet ondersteund.',

                },

            ]

        );



        if (! empty($data['parent_message_id'])) {
            $parentBelongsToConversation = DB::table('live_chat_messages')
                ->where('id', (int) $data['parent_message_id'])
                ->where('conversation_id', $conversation)
                ->exists();

            abort_unless(
                $parentBelongsToConversation,
                422,
                'Het bericht waarop je antwoordt hoort niet bij dit gesprek.'
            );
        }

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

        $emailDelivery = $email->deliverAdminMessage(

            $conversation,

            (string) $data['client_id']

        );

        $chat->setAdminTyping(
            $conversation,
            (int) $request->user()->id,
            false
        );



        return response()

            ->json([

                'ok' => true,

                'email_sent' =>

                    $emailDelivery['sent'],

                'email_skipped' =>

                    $emailDelivery['skipped'],

                'email_error' =>

                    $emailDelivery['error'],

            ])

            ->header(

                'Cache-Control',

                'no-store'

            );

    }




    public function attachment(
        Request $request,
        int $conversation,
        int $message
    ) {
        $this->authorizeAdmin($request);

        $record = DB::table('live_chat_messages')
            ->where('id', $message)
            ->where('conversation_id', $conversation)
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

    public function uploadStart(
        Request $request,
        LiveChatChunkUploadService $uploads,
        int $conversation
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $record = DB::table('live_chat_conversations')->where('id', $conversation)->first();
        abort_unless($record, 404);
        abort_if($record->status === 'closed', 409, 'Open het gesprek voordat je een video verstuurt.');

        $data = $request->validate([
            'client_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'mime' => ['nullable', 'string', 'max:100'],
            'size' => ['required', 'integer', 'min:1', 'max:1073741824'],
        ]);

        try {
            return response()->json(
                $uploads->start('admin:'.$request->user()->id.':conversation:'.$conversation, $data)
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function uploadStatus(
        Request $request,
        LiveChatChunkUploadService $uploads,
        int $conversation,
        string $upload
    ): JsonResponse {
        $this->authorizeAdmin($request);
        try {
            return response()->json(
                $uploads->status('admin:'.$request->user()->id.':conversation:'.$conversation, $upload)
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function uploadChunk(
        Request $request,
        LiveChatChunkUploadService $uploads,
        int $conversation,
        string $upload,
        int $index
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $request->validate([
            'chunk' => ['required', 'file', 'max:5120'],
        ]);

        try {
            return response()->json(
                $uploads->storeChunk(
                    'admin:'.$request->user()->id.':conversation:'.$conversation,
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
        LiveChatEmailService $email,
        LiveChatChunkUploadService $uploads,
        int $conversation,
        string $upload
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'parent_message_id' => ['nullable', 'integer', 'exists:live_chat_messages,id'],
        ]);

        if (! empty($data['parent_message_id'])) {
            $parentBelongsToConversation = DB::table('live_chat_messages')
                ->where('id', (int) $data['parent_message_id'])
                ->where('conversation_id', $conversation)
                ->exists();

            abort_unless(
                $parentBelongsToConversation,
                422,
                'Het bericht waarop je antwoordt hoort niet bij dit gesprek.'
            );
        }

        try {
            $attachment = $uploads->complete(
                'admin:'.$request->user()->id.':conversation:'.$conversation,
                $upload
            );

            $chat->sendAdmin(
                $conversation,
                (int) $request->user()->id,
                [
                    'client_id' => $attachment['client_id'],
                    'type' => 'video',
                    'body' => null,
                    'parent_message_id' => $data['parent_message_id'] ?? null,
                ],
                null,
                $attachment
            );

            $emailDelivery = $email->deliverAdminMessage(
                $conversation,
                (string) $attachment['client_id']
            );

            $chat->setAdminTyping($conversation, (int) $request->user()->id, false);

            return response()->json([
                'ok' => true,
                'email_sent' => $emailDelivery['sent'],
                'email_skipped' => $emailDelivery['skipped'],
                'email_error' => $emailDelivery['error'],
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
        LiveChatChunkUploadService $uploads,
        int $conversation,
        string $upload
    ): JsonResponse {
        $this->authorizeAdmin($request);
        try {
            $uploads->cancel('admin:'.$request->user()->id.':conversation:'.$conversation, $upload);
            return response()->json(['ok' => true]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function emailHandoff(
        Request $request,
        LiveChatEmailService $email,
        int $conversation
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'enabled' => [
                'required',
                'boolean',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'subject' => [
                'nullable',
                'string',
                'max:180',
            ],
            'title' => [
                'nullable',
                'string',
                'max:120',
            ],
        ]);

        if ((bool) $data['enabled']) {
            $record = DB::table(
                'live_chat_conversations as c'
            )
                ->leftJoin(
                    'users as u',
                    'u.id',
                    '=',
                    'c.user_id'
                )
                ->where(
                    'c.id',
                    $conversation
                )
                ->select(
                    'c.contact_email',
                    'c.email_subject',
                    'c.email_title',
                    'c.gmail_thread_id',
                    'u.email as user_email'
                )
                ->first();

            abort_unless($record, 404);

            $targetEmail = trim(
                (string) (
                    $data['email']
                    ?: $record->contact_email
                    ?: $record->user_email
                    ?: ''
                )
            );

            if ($targetEmail === '') {
                return response()->json(
                    [
                        'message' =>
                            'Vul eerst het e-mailadres van de klant in.',
                        'errors' => [
                            'email' => [
                                'Een e-mailadres is nodig om het gesprek via e-mail voort te zetten.',
                            ],
                        ],
                    ],
                    422
                );
            }

            $result = $email->enable(
                $conversation,
                $targetEmail,
                (int) $request->user()->id,
                isset($data['subject'])
                    ? (string) $data['subject']
                    : null,
                isset($data['title'])
                    ? (string) $data['title']
                    : null
            );
        } else {
            $result = $email->disable(
                $conversation
            );
        }

        return response()
            ->json(
                [
                    'ok' => true,
                ] + $result
            )
            ->header(
                'Cache-Control',
                'no-store'
            );
    }



    public function emailSync(
        Request $request,
        GmailLiveChatInboxService $gmail
    ): JsonResponse {
        $this->authorizeAdmin($request);

        try {
            $result = $gmail->sync();
        } catch (\Throwable $exception) {
            report($exception);

            $result = [
                'ok' => false,
                'configured' => true,
                'checked' => 0,
                'inserted' => 0,
                'duplicates' => 0,
                'errors' => 1,
                'message' => 'Gmail kon niet worden gesynchroniseerd. Controleer de Gmail API-koppeling via /admin/live-chat/gmail/connect.',
            ];
        }

        return response()
            ->json($result)
            ->header('Cache-Control', 'no-store');
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

                $data,
                $request

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

                        'status' => $data['status'],
                        'closed_at' => $data['status'] === 'closed' ? now() : null,
                        'closed_by_user_id' => $data['status'] === 'closed' ? (int) $request->user()->id : null,
                        'reopened_count' => $data['status'] === 'open' && $record->status === 'closed'
                            ? ((int) ($record->reopened_count ?? 0) + 1)
                            : (int) ($record->reopened_count ?? 0),

                        'updated_at' => now(),

                    ]);

                DB::table('live_chat_conversation_events')->insert([
                    'conversation_id' => $conversation,
                    'actor_user_id' => (int) $request->user()->id,
                    'event_type' => $data['status'] === 'closed' ? 'closed' : 'reopened',
                    'metadata' => null,
                    'created_at' => now(),
                ]);

            }

        );



        app(LiveChatService::class)->clearTyping($conversation);

        return response()->json([

            'ok' => true,

        ]);

    }



    public function typing(
        Request $request,
        LiveChatService $chat,
        int $conversation
    ): JsonResponse {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'typing' => [
                'required',
                'boolean',
            ],
        ]);

        $chat->setAdminTyping(
            $conversation,
            (int) $request->user()->id,
            (bool) $data['typing']
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
