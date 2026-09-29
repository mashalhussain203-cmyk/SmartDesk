<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chat_conversations', function (Blueprint $table): void {
            $table->string('delivery_channel', 20)
                ->default('live')
                ->index();

            $table->string('contact_email')
                ->nullable();

            $table->string('email_thread_token', 80)
                ->nullable()
                ->unique();

            $table->timestamp('email_handoff_at')
                ->nullable();
        });

        Schema::table('live_chat_messages', function (Blueprint $table): void {
            $table->string('source', 20)
                ->default('live')
                ->index();

            $table->string('email_message_id', 191)
                ->nullable()
                ->unique();

            $table->string('email_in_reply_to', 191)
                ->nullable();

            $table->timestamp('email_sent_at')
                ->nullable();

            $table->text('email_delivery_error')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('live_chat_messages', function (Blueprint $table): void {
            $table->dropIndex(['source']);
            $table->dropUnique(['email_message_id']);

            $table->dropColumn([
                'source',
                'email_message_id',
                'email_in_reply_to',
                'email_sent_at',
                'email_delivery_error',
            ]);
        });

        Schema::table('live_chat_conversations', function (Blueprint $table): void {
            $table->dropIndex(['delivery_channel']);
            $table->dropUnique(['email_thread_token']);

            $table->dropColumn([
                'delivery_channel',
                'contact_email',
                'email_thread_token',
                'email_handoff_at',
            ]);
        });
    }
};
