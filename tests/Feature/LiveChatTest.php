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


}
