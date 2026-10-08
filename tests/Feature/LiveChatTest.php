<?php

namespace Tests\Feature;

use Database\Factories\UserFactory;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

use Tests\TestCase;

class LiveChatTest extends TestCase

{

    protected function setUp(): void

    {

        parent::setUp();

        require_once base_path('database/factories/UserFactory.php');

        config([

            'app.key' => 'base64:'.base64_encode(str_repeat('l', 32)),

            'database.default' => 'sqlite',

            'database.connections.sqlite.database' => ':memory:',

            'database.connections.sqlite.url' => null,

            'session.driver' => 'array',

            'cache.default' => 'array',

            'login-security.enabled' => false,

        ]);

        DB::purge('sqlite');

        $this->artisan('migrate', [

            '--path' => [

                'database/migrations/0001_01_01_000000_create_users_table.php',

                'database/migrations/2026_09_15_232155_add_is_admin_to_users_tablee.php',

                'database/migrations/2026_09_17_132836_add_login_provider_to_users_table.php',

                'database/migrations/2026_09_29_210000_create_live_chat_tables.php',

                'database/migrations/2026_09_29_230958_add_media_and_sender_fields_to_live_chat_messages_table.php',

                'database/migrations/2026_09_30_000001_add_email_handoff_to_live_chat.php',
                'database/migrations/2026_09_30_000002_add_gmail_threading_to_live_chat.php',
                'database/migrations/2026_09_30_000003_add_email_title_to_live_chat.php',
                'database/migrations/2026_10_03_200000_upgrade_live_chat_features.php',
                'database/migrations/2026_10_03_220000_create_live_chat_calls_table.php',
                'database/migrations/2026_10_08_210000_add_guest_presence_to_live_chat_conversations.php',

            ],

            '--force' => true,

        ])->assertExitCode(0);

    }

    private function message(string $text = 'Hallo'): array

    {

        return [

            'body' => $text,

            'client_id' => (string) Str::uuid(),

            'type' => 'text',

        ];

    }

    public function test_guests_are_separated_and_cannot_spoof_account_identity(): void

    {

        $data = $this->message();

        $this

            ->withSession([

                'live_chat.guest_token' => str_repeat('a', 64),

            ])

            ->postJson(

                '/live-chat/messages',

                $data + [

                    'user_id' => 987,

                    'name' => 'Admin',

                    'email' => 'fake@example.test',

                ]

            )

            ->assertOk();

        $this->assertDatabaseHas(

            'live_chat_conversations',

            [

                'user_id' => null,

                'status' => 'waiting',

            ]

        );

        $this

            ->getJson('/live-chat')

            ->assertOk()

            ->assertJsonPath(

                'messages.0.body',

                'Hallo'

            )

            ->assertJsonMissingPath(

                'conversation.owner_key'

            )

            ->assertJsonMissingPath(

                'conversation.email'

            );

        $this

            ->withSession([

                'live_chat.guest_token' => str_repeat('b', 64),

            ])

            ->getJson(

                '/live-chat?conversation_id=1'

            )

            ->assertOk()

            ->assertJsonPath(

                'conversation',

                null

            )

            ->assertJsonCount(

                0,

                'messages'

            );

        $this

            ->postJson('/live-chat/reopen')

            ->assertNotFound();

        $this->assertDatabaseCount(

            'live_chat_messages',

            1

        );

    }

    public function test_account_name_and_email_come_from_authenticated_user_only(): void

    {

        $user = UserFactory::new()->create([

            'name' => 'Sanne',

            'email' => 'sanne@example.test',

        ]);

        $other = UserFactory::new()->create();

        $this

            ->actingAs($user)

            ->postJson(

                '/live-chat/messages',

                $this->message() + [

                    'user_id' => $other->id,

                    'name' => 'Fake',

                ]

            )

            ->assertOk();

        $this->assertDatabaseHas(

            'live_chat_conversations',

            [

                'user_id' => $user->id,

            ]

        );

        $this

            ->actingAs($other)

            ->getJson('/live-chat')

            ->assertOk()

            ->assertJsonPath(

                'conversation',

                null

            );

        $admin = UserFactory::new()->create([

            'is_admin' => true,

        ]);

        $this

            ->actingAs($admin)

            ->getJson(

                '/admin/live-chat/conversations'

            )

            ->assertOk()

            ->assertJsonPath(

                'items.0.name',

                'Sanne'

            )

            ->assertJsonPath(

                'items.0.email',

                'sanne@example.test'

            );

    }

    public function test_every_admin_action_rejects_a_regular_user(): void

    {

        $user = UserFactory::new()->create([

            'is_admin' => false,

        ]);

        $this->actingAs($user);

        $this

            ->getJson('/admin/live-chat')

            ->assertForbidden();

        $this

            ->getJson(

                '/admin/live-chat/conversations'

            )

            ->assertForbidden();

        $this

            ->getJson(

                '/admin/live-chat/conversations/1'

            )

            ->assertForbidden();

        $this

            ->postJson(

                '/admin/live-chat/conversations/1/messages',

                $this->message()

            )

            ->assertForbidden();

        $this

            ->patchJson(

                '/admin/live-chat/conversations/1',

                [

                    'status' => 'closed',

                ]

            )

            ->assertForbidden();

        $this

            ->postJson(

                '/admin/live-chat/conversations/1/email-handoff',

                [

                    'enabled' => true,

                    'email' => 'klant@example.test',

                ]

            )

            ->assertForbidden();

        $this

            ->postJson(

                '/admin/live-chat/email-sync',

                []

            )

            ->assertForbidden();

        $this

            ->postJson(

                '/admin/live-chat/presence',

                [

                    'online' => true,

                ]

            )

            ->assertForbidden();

        $this->assertDatabaseCount(

            'live_chat_messages',

            0

        );

    }

    public function test_anonymous_users_cannot_access_admin_inbox(): void

    {

        $this

            ->getJson(

                '/admin/live-chat/conversations'

            )

            ->assertUnauthorized();

    }

    public function test_retry_does_not_duplicate_a_message_and_changed_retry_is_rejected(): void

    {

        $data = $this->message();

        $this

            ->withSession([

                'live_chat.guest_token' => str_repeat('c', 64),

            ])

            ->postJson(

                '/live-chat/messages',

                $data

            )

            ->assertOk();

        $this

            ->postJson(

                '/live-chat/messages',

                $data

            )

            ->assertOk();

        $this

            ->postJson(

                '/live-chat/messages',

                [

                    'body' => 'Andere inhoud',

                    'client_id' => $data['client_id'],

                    'type' => 'text',

                ]

            )

            ->assertConflict();

        $this->assertDatabaseCount(

            'live_chat_messages',

            1

        );

    }

    public function test_admin_reply_close_reopen_and_unread_state(): void

    {

        $visitor = UserFactory::new()->create();

        $admin = UserFactory::new()->create([

            'is_admin' => true,

        ]);

        $conversationId = $this

            ->actingAs($visitor)

            ->postJson(

                '/live-chat/messages',

                $this->message()

            )

            ->assertOk()

            ->json('conversation.id');

        $this

            ->actingAs($admin)

            ->getJson(

                '/admin/live-chat/conversations'

            )

            ->assertOk()

            ->assertJsonPath(

                'items.0.unread',

                1

            );

        $this

            ->getJson(

                '/admin/live-chat/conversations/'.$conversationId

            )

            ->assertOk();

        $this

            ->getJson(

                '/admin/live-chat/conversations'

            )

            ->assertOk()

            ->assertJsonPath(

                'items.0.unread',

                0

            );

        $reply = $this->message(

            'Ik help je graag.'

        );

        $this

            ->postJson(

                '/admin/live-chat/conversations/'.$conversationId.'/messages',

                $reply

            )

            ->assertOk();

        $this

            ->postJson(

                '/admin/live-chat/conversations/'.$conversationId.'/messages',

                $reply

            )

            ->assertOk();

        $this->assertDatabaseCount(

            'live_chat_messages',

            2

        );

        $this

            ->actingAs($visitor)

            ->getJson('/live-chat')

            ->assertOk()

            ->assertJsonPath(

                'messages.1.sender',

                'admin'

            )

            ->assertJsonPath(

                'messages.1.body',

                'Ik help je graag.'

            );

        $this

            ->actingAs($admin)

            ->patchJson(

                '/admin/live-chat/conversations/'.$conversationId,

                [

                    'status' => 'closed',

                ]

            )

            ->assertOk();

        $this

            ->actingAs($visitor)

            ->postJson(

                '/live-chat/messages',

                $this->message()

            )

            ->assertConflict();

        $this

            ->postJson('/live-chat/reopen')

            ->assertOk();

        $this

            ->postJson(

                '/live-chat/messages',

                $this->message('Bedankt')

            )

            ->assertOk();

        $this->assertDatabaseHas(

            'live_chat_conversations',

            [

                'id' => $conversationId,

                'status' => 'waiting',

            ]

        );

        $this->assertDatabaseCount(

            'live_chat_messages',

            3

        );

    }

    public function test_presence_expires_and_non_admin_presence_is_not_counted(): void

    {

        $this->freezeTime();

        $admin = UserFactory::new()->create([

            'is_admin' => true,

        ]);

        $this

            ->actingAs($admin)

            ->postJson(

                '/admin/live-chat/presence',

                [

                    'online' => true,

                ]

            )

            ->assertOk();

        $this

            ->getJson('/live-chat')

            ->assertOk()

            ->assertJsonPath(

                'online',

                true

            );

        $this

            ->travel(61)

            ->seconds();

        $this

            ->getJson('/live-chat')

            ->assertOk()

            ->assertJsonPath(

                'online',

                false

            );

        $this

            ->actingAs($admin)

            ->postJson(

                '/admin/live-chat/presence',

                [

                    'online' => true,

                ]

            )

            ->assertOk();

        $admin->update([

            'is_admin' => false,

        ]);

        $this

            ->getJson('/live-chat')

            ->assertOk()

            ->assertJsonPath(

                'online',

                false

            );

    }

    public function test_invalid_message_is_not_stored(): void

    {

        $this

            ->postJson(

                '/live-chat/messages',

                []

            )

            ->assertUnprocessable()

            ->assertJsonValidationErrors([

                'body',

                'client_id',

            ]);

        $this

            ->postJson(

                '/live-chat/messages',

                [

                    'body' => str_repeat(

                        'x',

                        4001

                    ),

                    'client_id' => 'invalid',

                    'type' => 'text',

                ]

            )

            ->assertUnprocessable()

            ->assertJsonValidationErrors([

                'body',

                'client_id',

            ]);

        $this->assertDatabaseCount(

            'live_chat_conversations',

            0

        );

        $this->assertDatabaseCount(

            'live_chat_messages',

            0

        );

    }


    public function test_chat_launcher_routes_admin_to_operator_inbox_and_user_to_live_chat(): void
    {
        $admin = UserFactory::new()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('id="admin-live-chat-launcher"', false)
            ->assertSee('href="'.route('admin.live-chat.index').'"', false)
            ->assertDontSee('id="guest-chat"', false);

        $user = UserFactory::new()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('id="guest-chat"', false)
            ->assertSee('data-default-mode="human"', false)
            ->assertDontSee('id="admin-live-chat-launcher"', false);

        auth()->logout();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="guest-chat"', false)
            ->assertDontSee('id="admin-live-chat-launcher"', false);
    }

    public function test_admin_user_management_has_operator_launcher_not_a_visitor_chat(): void
    {
        $admin = UserFactory::new()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('id="admin-live-chat-launcher"', false)
            ->assertDontSee('id="guest-chat"', false);
    }



    public function test_guest_chat_disappears_from_admin_after_leaving_but_messages_remain(): void
    {
        $this->freezeTime();

        $token = str_repeat('g', 64);

        $this->withSession(['live_chat.guest_token' => $token])
            ->postJson('/live-chat/messages', $this->message('Gast online'))
            ->assertOk();

        $guest = DB::table('live_chat_conversations')->whereNull('user_id')->first();

        $this->assertNotNull($guest);
        $this->assertNotNull($guest->visitor_last_seen_at);

        $admin = UserFactory::new()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->getJson('/admin/live-chat/conversations')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.kind', 'guest');

        // Guests disappear without deleting messages or their conversation.
        $this->travel(46)->seconds();

        $this->getJson('/admin/live-chat/conversations')
            ->assertOk()
            ->assertJsonCount(0, 'items');

        $this->assertDatabaseHas('live_chat_messages', [
            'conversation_id' => $guest->id,
            'body' => 'Gast online',
        ]);

        // Returning in the same browser/session makes the guest visible again.
        auth()->logout();

        $this->withSession(['live_chat.guest_token' => $token])
            ->getJson('/live-chat')
            ->assertOk();

        $this->actingAs($admin)
            ->getJson('/admin/live-chat/conversations')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.kind', 'guest');
    }

    public function test_real_account_chat_remains_in_admin_after_visitor_goes_offline(): void
    {
        $this->freezeTime();

        $account = UserFactory::new()->create([
            'name' => 'Account Bezoeker',
            'email' => 'account-bezoeker@example.test',
            'is_admin' => false,
        ]);

        $this->actingAs($account)
            ->postJson('/live-chat/messages', $this->message('Account bericht'))
            ->assertOk();

        $admin = UserFactory::new()->create(['is_admin' => true]);

        $this->travel(120)->seconds();

        $this->actingAs($admin)
            ->getJson('/admin/live-chat/conversations')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.kind', 'account')
            ->assertJsonPath('items.0.name', 'Account Bezoeker');
    }



    public function test_guest_can_start_audio_call_without_sending_a_message(): void
    {
        $this->freezeTime();
        $admin = UserFactory::new()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->postJson('/admin/live-chat/presence', ['online' => true])
            ->assertOk();

        auth()->logout();

        $token = str_repeat('v', 64);

        $this->withSession(['live_chat.guest_token' => $token])
            ->postJson('/live-chat/calls', [
                'mode' => 'audio',
                'offer' => ['type' => 'offer', 'sdp' => "v=0\\r\\n"],
            ])
            ->assertCreated()
            ->assertJsonPath('call.initiated_by', 'visitor')
            ->assertJsonPath('call.mode', 'audio')
            ->assertJsonPath('call.status', 'ringing');

        $this->assertDatabaseCount('live_chat_messages', 0);
        $conversation = DB::table('live_chat_conversations')->whereNull('user_id')->first();
        $this->assertNotNull($conversation);
        $this->assertNotNull($conversation->visitor_last_seen_at);

        $this->actingAs($admin)
            ->getJson('/admin/live-chat/conversations/'.$conversation->id.'/calls/current')
            ->assertOk()
            ->assertJsonPath('call.initiated_by', 'visitor')
            ->assertJsonPath('call.status', 'ringing');

        $this->actingAs($admin)
            ->getJson('/admin/live-chat/calls/incoming')
            ->assertOk()
            ->assertJsonPath('call.initiated_by', 'visitor')
            ->assertJsonPath('call.mode', 'audio')
            ->assertJsonPath('call.caller_name', 'Gast #'.$conversation->id);
    }

    public function test_guest_can_start_video_call_and_admin_can_answer(): void
    {
        $admin = UserFactory::new()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->postJson('/admin/live-chat/presence', ['online' => true])
            ->assertOk();
        auth()->logout();

        $token = str_repeat('w', 64);
        $response = $this->withSession(['live_chat.guest_token' => $token])
            ->postJson('/live-chat/calls', [
                'mode' => 'video',
                'offer' => ['type' => 'offer', 'sdp' => "v=0\\r\\n"],
            ])
            ->assertCreated()
            ->assertJsonPath('call.mode', 'video');

        $callId = (string) $response->json('call.id');
        $conversationId = (int) $response->json('call.conversation_id');
        $this->assertNotSame('', $callId);

        $this->actingAs($admin)
            ->postJson('/admin/live-chat/conversations/'.$conversationId.'/calls/'.$callId.'/answer', [
                'answer' => ['type' => 'answer', 'sdp' => "v=0\\r\\n"],
            ])
            ->assertOk()
            ->assertJsonPath('call.status', 'accepted');

        auth()->logout();
        $this->withSession(['live_chat.guest_token' => $token])
            ->getJson('/live-chat/calls/current')
            ->assertOk()
            ->assertJsonPath('call.id', $callId)
            ->assertJsonPath('call.mode', 'video')
            ->assertJsonPath('call.status', 'accepted');
    }

    public function test_guest_cannot_start_call_if_admin_is_offline(): void
    {
        $this->withSession(['live_chat.guest_token' => str_repeat('x', 64)])
            ->postJson('/live-chat/calls', [
                'mode' => 'audio',
                'offer' => ['type' => 'offer', 'sdp' => "v=0\\r\\n"],
            ])
            ->assertStatus(409);

        $this->assertDatabaseCount('live_chat_conversations', 0);
        $this->assertDatabaseCount('live_chat_calls', 0);
    }

    public function test_guest_call_buttons_are_shown_in_human_support_mode(): void
    {
        $callsScript = file_get_contents(public_path('js/live-chat-calls.js'));
        $chatScript = file_get_contents(public_path('js/live-chat.js'));
        $guestView = file_get_contents(resource_path('views/site/partials/guest-chat.blade.php'));

        $this->assertIsString($callsScript);
        $this->assertIsString($chatScript);
        $this->assertIsString($guestView);
        $this->assertStringContainsString("const live = root.dataset.mode === 'human';", $callsScript);
        $this->assertStringContainsString('lc-call-actions', $callsScript);
        $this->assertStringContainsString('Spraakoproep', $callsScript);
        $this->assertStringContainsString('Videogesprek', $callsScript);
        $this->assertStringContainsString("'human'", $chatScript);
        $this->assertStringContainsString('js/live-chat-calls.js', $guestView);
    }



    public function test_guest_has_immediately_accessible_audio_and_video_buttons_in_chat_header(): void
    {
        $callsScript = file_get_contents(public_path('js/live-chat-calls.js'));
        $guestView = file_get_contents(resource_path('views/site/partials/guest-chat.blade.php'));
        $adminView = file_get_contents(resource_path('views/admin/live-chat.blade.php'));

        $this->assertIsString($callsScript);
        $this->assertIsString($guestView);
        $this->assertIsString($adminView);
        $this->assertStringContainsString('function installGuestHeaderButtons()', $callsScript);
        $this->assertStringContainsString("root.querySelector('.guest-chat__header .gc-actions')", $callsScript);
        $this->assertStringContainsString('Spraakbellen met de admin', $callsScript);
        $this->assertStringContainsString('Videobellen met de admin', $callsScript);
        $this->assertStringContainsString("void startOutgoing(mode)", $callsScript);
        $this->assertStringContainsString("root.dispatchEvent(new CustomEvent('live-chat:handoff'", $callsScript);
        $this->assertStringContainsString("root.querySelector('.lca-heading__actions')", $callsScript);
        $this->assertStringContainsString("side === 'admin' && call.initiated_by === 'visitor'", $callsScript);
        $this->assertStringContainsString("js/live-chat-calls.js') }}?v=7", $guestView);
        $this->assertStringContainsString("js/live-chat-calls.js') }}?v=7", $adminView);
        $this->assertStringContainsString('data-default-mode="human"', $guestView);
    }

}
