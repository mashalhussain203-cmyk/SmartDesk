<?php



namespace App\Services;



use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use stdClass;



class LiveChatService

{

    /**

     * Geeft een unieke eigenaarssleutel terug voor een ingelogde gebruiker

     * of voor een gast op basis van de huidige sessie.

     */

    public function ownerKey(Request $request): string

    {

        if ($request->user()) {

            return 'user:'.$request->user()->getAuthIdentifier();

        }



        $token = $request->session()->get('live_chat.guest_token');



        if (! is_string($token) || strlen($token) !== 64) {

            $token = Str::random(64);



            $request->session()->put(

                'live_chat.guest_token',

                $token

            );

        }



        return 'guest:'.hash('sha256', $token);

    }



    /**

     * Haalt het gesprek op dat bij de huidige bezoeker hoort.

     */

    public function visitorConversation(Request $request): ?stdClass

    {

        return DB::table('live_chat_conversations')

            ->where('owner_key', $this->ownerKey($request))

            ->first();

    }



    /**

     * Controleert of er momenteel een admin/medewerker online is.

     */

    public function online(): bool

    {

        return DB::table('live_chat_agents')

            ->join(

                'users',

                'users.id',

                '=',

                'live_chat_agents.user_id'

            )

            ->where('users.is_admin', true)

            ->where(

                'live_chat_agents.last_seen_at',

                '>',

                now()->subSeconds(60)

            )

            ->exists();

    }



    /**

     * Geeft naam en profielfoto terug van een gebruiker.

     *

     * @return array{

     *     name: string|null,

     *     avatar: string|null

     * }

     */

    public function userIdentity(?int $userId): array

    {

        if (! $userId) {

            return [

                'name' => null,

                'avatar' => null,

            ];

        }



        $user = User::query()->find($userId);



        if (! $user) {

            return [

                'name' => null,

                'avatar' => null,

            ];

        }



        $avatar = null;



        if (method_exists($user, 'avatarUrl')) {

            $avatar = $user->avatarUrl();

        } elseif (

            isset($user->profile_photo_path)

            && $user->profile_photo_path

        ) {

            $avatar = Storage::url(

                $user->profile_photo_path

            );

        } elseif (

            isset($user->avatar)

            && $user->avatar

        ) {

            $avatar = Storage::url(

                $user->avatar

            );

        }



        return [

            'name' => $user->name,

            'avatar' => $avatar ?: null,

        ];

    }



    /**

     * Bouwt de JSON-data voor het chatvenster.

     *

     * @return array<string, mixed>

     */

    public function payload(

        ?stdClass $conversation,

        int $after = 0

    ): array {

        $messages = collect();



        if ($conversation) {

            $query = DB::table('live_chat_messages')

                ->where(

                    'conversation_id',

                    $conversation->id

                );



            if ($after > 0) {

                $messages = $query

                    ->where('id', '>', $after)

                    ->orderBy('id')

                    ->limit(100)

                    ->get();

            } else {

                $messages = $query

                    ->orderByDesc('id')

                    ->limit(100)

                    ->get()

                    ->reverse()

                    ->values();

            }

        }



        $identities = [];



        foreach (

            $messages

                ->pluck('sender_user_id')

                ->filter()

                ->unique()

            as $userId

        ) {

            $identities[(int) $userId] =

                $this->userIdentity((int) $userId);

        }



        $visitorTyping = $this->typingState(
            $conversation,
            'visitor'
        );

        $adminTyping = $this->typingState(
            $conversation,
            'admin'
        );

        return [

            'conversation' => $conversation

                ? [

                    'id' => $conversation->id,

                    'status' => $conversation->status,

                ]

                : null,



            'online' => $this->online(),

            'visitor_typing' => $visitorTyping['active'],

            'admin_typing' => $adminTyping['active'],

            'typing' => [
                'visitor' => $visitorTyping,
                'admin' => $adminTyping,
            ],



            'messages' => $messages

                ->map(

                    function (

                        stdClass $message

                    ) use (

                        $conversation,

                        $identities

                    ): array {

                        $identity = [

                            'name' => null,

                            'avatar' => null,

                        ];



                        if ($message->sender_user_id) {

                            $identity =

                                $identities[

                                    (int) $message->sender_user_id

                                ]

                                ?? [

                                    'name' => null,

                                    'avatar' => null,

                                ];

                        }



                        if (

                            $message->sender === 'admin'

                        ) {

                            $senderName =

                                $identity['name']

                                ?: 'Medewerker';

                        } elseif (

                            $conversation?->user_id

                        ) {

                            $senderName =

                                $identity['name']

                                ?: 'Gebruiker';

                        } else {

                            $senderName =

                                'Gast #'.

                                ($conversation?->id ?? '');

                        }



                        $attachmentUrl = null;



                        if (

                            ! empty(

                                $message->attachment_path

                            )

                        ) {

                            $attachmentUrl =

                                Storage::url(

                                    $message->attachment_path

                                );

                        }



                        return [

                            'id' => $message->id,



                            'client_id' =>

                                $message->client_id,



                            'sender' =>

                                $message->sender,



                            'sender_name' =>

                                $senderName,



                            'sender_avatar' =>

                                $identity['avatar'],



                            'type' =>

                                $message->type

                                ?? 'text',



                            'body' =>

                                $message->body

                                ?? '',



                            'attachment_name' =>

                                $message->attachment_name

                                ?? null,



                            'attachment_mime' =>

                                $message->attachment_mime

                                ?? null,



                            'attachment_size' =>

                                $message->attachment_size

                                ?? null,



                            'attachment_url' =>

                                $attachmentUrl,



                            'created_at' =>

                                $message->created_at,

                        ];

                    }

                )

                ->all(),

        ];

    }



    /**
     * Houdt een tijdelijke typing-status bij voor de bezoeker.
     *
     * De status staat bewust in Laravel Cache en niet in MySQL. Daardoor
     * schrijven we niet bij iedere toetsaanslag naar de database.
     */
    public function setVisitorTyping(
        Request $request,
        bool $typing
    ): ?stdClass {
        $conversation = $this->visitorConversation($request);

        if (! $conversation) {
            return null;
        }

        if (! $typing || $conversation->status === 'closed') {
            Cache::forget(
                $this->typingKey((int) $conversation->id, 'visitor')
            );

            return $conversation;
        }

        Cache::put(
            $this->typingKey((int) $conversation->id, 'visitor'),
            [
                'user_id' => $request->user()?->getAuthIdentifier(),
                'at' => now()->timestamp,
            ],
            now()->addSeconds(6)
        );

        return $conversation;
    }

    /**
     * Houdt een tijdelijke typing-status bij voor de medewerker.
     */
    public function setAdminTyping(
        int $conversationId,
        int $adminId,
        bool $typing
    ): void {
        $conversation = DB::table('live_chat_conversations')
            ->where('id', $conversationId)
            ->first();

        abort_unless($conversation, 404);

        if (! $typing || $conversation->status === 'closed') {
            Cache::forget(
                $this->typingKey($conversationId, 'admin')
            );

            return;
        }

        Cache::put(
            $this->typingKey($conversationId, 'admin'),
            [
                'user_id' => $adminId,
                'at' => now()->timestamp,
            ],
            now()->addSeconds(6)
        );
    }

    /**
     * Verwijdert alle tijdelijke typing-status voor één gesprek.
     */
    public function clearTyping(int $conversationId): void
    {
        Cache::forget(
            $this->typingKey($conversationId, 'visitor')
        );

        Cache::forget(
            $this->typingKey($conversationId, 'admin')
        );
    }

    /**
     * @return array{active: bool, name: string|null, avatar: string|null}
     */
    private function typingState(
        ?stdClass $conversation,
        string $sender
    ): array {
        if (! $conversation || $conversation->status === 'closed') {
            return [
                'active' => false,
                'name' => null,
                'avatar' => null,
            ];
        }

        $state = Cache::get(
            $this->typingKey((int) $conversation->id, $sender)
        );

        if (! is_array($state)) {
            return [
                'active' => false,
                'name' => null,
                'avatar' => null,
            ];
        }

        $userId = isset($state['user_id']) && $state['user_id']
            ? (int) $state['user_id']
            : null;

        if ($sender === 'admin') {
            $identity = $this->userIdentity($userId);

            return [
                'active' => true,
                'name' => $identity['name'] ?: 'Medewerker',
                'avatar' => $identity['avatar'],
            ];
        }

        if ($userId) {
            $identity = $this->userIdentity($userId);

            return [
                'active' => true,
                'name' => $identity['name'] ?: 'Bezoeker',
                'avatar' => $identity['avatar'],
            ];
        }

        return [
            'active' => true,
            'name' => 'Gast #'.(int) $conversation->id,
            'avatar' => null,
        ];
    }

    private function typingKey(
        int $conversationId,
        string $sender
    ): string {
        return 'live-chat:typing:'.$conversationId.':'.$sender;
    }


    /**

     * Stuurt een bericht namens een bezoeker.

     *

     * @param array{

     *     body?: string|null,

     *     client_id: string,

     *     type?: string

     * } $data

     */

    public function sendVisitor(

        Request $request,

        array $data,

        ?UploadedFile $file = null

    ): stdClass {

        $owner = $this->ownerKey($request);



        return DB::transaction(

            function () use (

                $request,

                $data,

                $file,

                $owner

            ): stdClass {

                DB::table(

                    'live_chat_conversations'

                )->insertOrIgnore([

                    'owner_key' => $owner,



                    'user_id' =>

                        $request

                            ->user()

                            ?->getAuthIdentifier(),



                    'status' => 'waiting',



                    'created_at' => now(),

                    'updated_at' => now(),

                ]);



                $conversation =

                    DB::table(

                        'live_chat_conversations'

                    )

                    ->where(

                        'owner_key',

                        $owner

                    )

                    ->lockForUpdate()

                    ->first();



                abort_unless(

                    $conversation,

                    503

                );



                abort_if(

                    $conversation->status

                        === 'closed',

                    409,

                    'Dit gesprek is gesloten. Start het gesprek opnieuw.'

                );



                $existing =

                    DB::table(

                        'live_chat_messages'

                    )

                    ->where(

                        'conversation_id',

                        $conversation->id

                    )

                    ->where(

                        'client_id',

                        $data['client_id']

                    )

                    ->first();



                if ($existing) {

                    abort_unless(

                        $existing->sender

                            === 'visitor'

                        && (

                            $existing->type

                            ?? 'text'

                        )

                            === (

                                $data['type']

                                ?? 'text'

                            )

                        && (string) (

                            $existing->body

                            ?? ''

                        )

                            === (string) (

                                $data['body']

                                ?? ''

                            ),

                        409

                    );



                    return $conversation;

                }



                $this->insertMessage(

                    (int) $conversation->id,

                    'visitor',

                    $request

                        ->user()

                        ?->getAuthIdentifier(),

                    $data,

                    $file

                );



                DB::table(

                    'live_chat_conversations'

                )

                    ->where(

                        'id',

                        $conversation->id

                    )

                    ->update([

                        'status' =>

                            'waiting',



                        'updated_at' =>

                            now(),

                    ]);



                return DB::table(

                    'live_chat_conversations'

                )

                    ->where(

                        'id',

                        $conversation->id

                    )

                    ->first();

            }

        );

    }



    /**

     * Stuurt een bericht namens een medewerker.

     *

     * @param array{

     *     body?: string|null,

     *     client_id: string,

     *     type?: string

     * } $data

     */

    public function sendAdmin(

        int $conversationId,

        int $adminId,

        array $data,

        ?UploadedFile $file = null

    ): stdClass {

        return DB::transaction(

            function () use (

                $conversationId,

                $adminId,

                $data,

                $file

            ): stdClass {

                $conversation =

                    DB::table(

                        'live_chat_conversations'

                    )

                    ->where(

                        'id',

                        $conversationId

                    )

                    ->lockForUpdate()

                    ->first();



                abort_unless(

                    $conversation,

                    404

                );



                abort_if(

                    $conversation->status

                        === 'closed',

                    409,

                    'Open het gesprek voordat je antwoordt.'

                );



                $existing =

                    DB::table(

                        'live_chat_messages'

                    )

                    ->where(

                        'conversation_id',

                        $conversationId

                    )

                    ->where(

                        'client_id',

                        $data['client_id']

                    )

                    ->first();



                if ($existing) {

                    abort_unless(

                        $existing->sender

                            === 'admin'

                        && (

                            $existing->type

                            ?? 'text'

                        )

                            === (

                                $data['type']

                                ?? 'text'

                            )

                        && (string) (

                            $existing->body

                            ?? ''

                        )

                            === (string) (

                                $data['body']

                                ?? ''

                            ),

                        409

                    );



                    return $conversation;

                }



                $this->insertMessage(

                    $conversationId,

                    'admin',

                    $adminId,

                    $data,

                    $file

                );



                DB::table(

                    'live_chat_conversations'

                )

                    ->where(

                        'id',

                        $conversationId

                    )

                    ->update([

                        'status' => 'open',

                        'updated_at' => now(),

                    ]);



                return DB::table(

                    'live_chat_conversations'

                )

                    ->where(

                        'id',

                        $conversationId

                    )

                    ->first();

            }

        );

    }



    /**

     * Verwijdert een eigen bezoekersbericht.

     */

    public function deleteVisitorMessage(

        Request $request,

        int $messageId

    ): void {

        $conversation =

            $this->visitorConversation(

                $request

            );



        abort_unless(

            $conversation,

            404

        );



        $message =

            DB::table(

                'live_chat_messages'

            )

            ->where(

                'id',

                $messageId

            )

            ->where(

                'conversation_id',

                $conversation->id

            )

            ->where(

                'sender',

                'visitor'

            )

            ->first();



        abort_unless(

            $message,

            404

        );



        /*

        |--------------------------------------------------------------------------

        | Extra bescherming voor ingelogde accounts

        |--------------------------------------------------------------------------

        |

        | Als het gesprek bij een ingelogde gebruiker hoort, controleren we

        | ook of het bericht daadwerkelijk van die gebruiker afkomstig is.

        |

        */

        if ($request->user()) {

            abort_unless(

                (int) $message->sender_user_id

                    === (int) $request

                        ->user()

                        ->getAuthIdentifier(),

                403

            );

        }



        $this->deleteMessageRecord(

            $message

        );

    }



    /**

     * Verwijdert een bericht vanuit het adminpaneel.

     */

    public function deleteAdminMessage(

        int $conversationId,

        int $messageId

    ): void {

        $message =

            DB::table(

                'live_chat_messages'

            )

            ->where(

                'id',

                $messageId

            )

            ->where(

                'conversation_id',

                $conversationId

            )

            ->first();



        abort_unless(

            $message,

            404

        );



        $this->deleteMessageRecord(

            $message

        );

    }



    /**

     * Slaat een nieuw chatbericht op.

     *

     * @param array{

     *     body?: string|null,

     *     client_id: string,

     *     type?: string

     * } $data

     */

    private function insertMessage(

        int $conversationId,

        string $sender,

        ?int $senderUserId,

        array $data,

        ?UploadedFile $file

    ): void {

        $type =

            $data['type']

            ?? 'text';



        $body =

            trim(

                (string) (

                    $data['body']

                    ?? ''

                )

            );



        $path = null;

        $attachmentName = null;

        $attachmentMime = null;

        $attachmentSize = null;



        if ($file) {

            $folder =

                $type === 'voice'

                    ? 'live-chat/voice'

                    : 'live-chat/attachments';



            $path =

                $file->store(

                    $folder,

                    'public'

                );



            $attachmentName =

                $file

                    ->getClientOriginalName();



            $attachmentMime =

                $file->getMimeType();



            $attachmentSize =

                $file->getSize();

        }



        DB::table(

            'live_chat_messages'

        )->insert([

            'conversation_id' =>

                $conversationId,



            'client_id' =>

                $data['client_id'],



            'sender' =>

                $sender,



            'sender_user_id' =>

                $senderUserId,



            'type' =>

                $type,



            'body' =>

                $body,



            'attachment_path' =>

                $path,



            'attachment_name' =>

                $attachmentName,



            'attachment_mime' =>

                $attachmentMime,



            'attachment_size' =>

                $attachmentSize,



            'created_at' =>

                now(),

        ]);



        DB::table(

            'live_chat_conversations'

        )

            ->where(

                'id',

                $conversationId

            )

            ->update([

                'last_message_at' =>

                    now(),



                'updated_at' =>

                    now(),

            ]);

    }



    /**

     * Verwijdert het bericht en eventueel het bijbehorende bestand.

     */

    private function deleteMessageRecord(

        stdClass $message

    ): void {

        DB::transaction(

            function () use (

                $message

            ): void {

                if (

                    ! empty(

                        $message->attachment_path

                    )

                ) {

                    Storage::disk(

                        'public'

                    )->delete(

                        $message->attachment_path

                    );

                }



                DB::table(

                    'live_chat_messages'

                )

                    ->where(

                        'id',

                        $message->id

                    )

                    ->delete();

            }

        );

    }

}