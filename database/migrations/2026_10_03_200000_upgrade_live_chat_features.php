<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chat_conversations', function (Blueprint $table): void {
            if (! Schema::hasColumn('live_chat_conversations', 'assigned_admin_id')) {
                $table->foreignId('assigned_admin_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('live_chat_conversations', 'priority')) {
                $table->string('priority', 16)->default('normal')->after('status')->index();
            }
            if (! Schema::hasColumn('live_chat_conversations', 'labels')) {
                $table->json('labels')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('live_chat_conversations', 'visitor_last_read_id')) {
                $table->unsignedBigInteger('visitor_last_read_id')->default(0)->after('admin_last_read_id');
            }
            if (! Schema::hasColumn('live_chat_conversations', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('last_message_at');
            }
            if (! Schema::hasColumn('live_chat_conversations', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('first_response_at');
            }
            if (! Schema::hasColumn('live_chat_conversations', 'closed_by_user_id')) {
                $table->foreignId('closed_by_user_id')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('live_chat_conversations', 'reopened_count')) {
                $table->unsignedInteger('reopened_count')->default(0)->after('closed_by_user_id');
            }
            if (! Schema::hasColumn('live_chat_conversations', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('reopened_count');
            }
            if (! Schema::hasColumn('live_chat_conversations', 'blocked_reason')) {
                $table->string('blocked_reason', 255)->nullable()->after('blocked_at');
            }
            if (! Schema::hasColumn('live_chat_conversations', 'visitor_ip_hash')) {
                $table->string('visitor_ip_hash', 64)->nullable()->after('blocked_reason')->index();
            }
            if (! Schema::hasColumn('live_chat_conversations', 'auto_reply_sent_at')) {
                $table->timestamp('auto_reply_sent_at')->nullable()->after('visitor_ip_hash');
            }
        });

        Schema::table('live_chat_messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('live_chat_messages', 'parent_message_id')) {
                $table->unsignedBigInteger('parent_message_id')->nullable()->after('sender_user_id')->index();
            }
            if (! Schema::hasColumn('live_chat_messages', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('body');
            }
            if (! Schema::hasColumn('live_chat_messages', 'metadata')) {
                $table->json('metadata')->nullable()->after('edited_at');
            }
        });

        if (! Schema::hasTable('live_chat_internal_notes')) {
            Schema::create('live_chat_internal_notes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('conversation_id')->constrained('live_chat_conversations')->cascadeOnDelete();
                $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
                $table->index(['conversation_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('live_chat_message_reactions')) {
            Schema::create('live_chat_message_reactions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('message_id')->constrained('live_chat_messages')->cascadeOnDelete();
                $table->string('actor_key', 96);
                $table->string('emoji', 16);
                $table->timestamp('created_at');
                $table->unique(['message_id', 'actor_key', 'emoji'], 'live_chat_reaction_unique');
                $table->index(['message_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('live_chat_conversation_events')) {
            Schema::create('live_chat_conversation_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('conversation_id')->constrained('live_chat_conversations')->cascadeOnDelete();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event_type', 48);
                $table->json('metadata')->nullable();
                $table->timestamp('created_at');
                $table->index(['conversation_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('live_chat_blocks')) {
            Schema::create('live_chat_blocks', function (Blueprint $table): void {
                $table->id();
                $table->string('owner_key', 80)->nullable()->index();
                $table->string('ip_hash', 64)->nullable()->index();
                $table->string('reason', 255)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('live_chat_blocks');
        Schema::dropIfExists('live_chat_conversation_events');
        Schema::dropIfExists('live_chat_message_reactions');
        Schema::dropIfExists('live_chat_internal_notes');

        Schema::table('live_chat_messages', function (Blueprint $table): void {
            foreach (['metadata', 'edited_at', 'parent_message_id'] as $column) {
                if (Schema::hasColumn('live_chat_messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('live_chat_conversations', function (Blueprint $table): void {
            if (Schema::hasColumn('live_chat_conversations', 'assigned_admin_id')) {
                $table->dropConstrainedForeignId('assigned_admin_id');
            }
            if (Schema::hasColumn('live_chat_conversations', 'closed_by_user_id')) {
                $table->dropConstrainedForeignId('closed_by_user_id');
            }
            foreach ([
                'auto_reply_sent_at', 'visitor_ip_hash', 'blocked_reason', 'blocked_at',
                'reopened_count', 'closed_at', 'first_response_at',
                'visitor_last_read_id', 'labels', 'priority',
            ] as $column) {
                if (Schema::hasColumn('live_chat_conversations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
