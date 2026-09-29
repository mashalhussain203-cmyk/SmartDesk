<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('live_chat_messages')) {
            return;
        }

        if (! Schema::hasColumn('live_chat_messages', 'sender_user_id')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->unsignedBigInteger('sender_user_id')->nullable();
            });
        }

        if (! Schema::hasColumn('live_chat_messages', 'type')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->string('type', 20)->default('text');
            });
        }

        if (! Schema::hasColumn('live_chat_messages', 'attachment_path')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->string('attachment_path')->nullable();
            });
        }

        if (! Schema::hasColumn('live_chat_messages', 'attachment_name')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->string('attachment_name')->nullable();
            });
        }

        if (! Schema::hasColumn('live_chat_messages', 'attachment_mime')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->string('attachment_mime', 150)->nullable();
            });
        }

        if (! Schema::hasColumn('live_chat_messages', 'attachment_size')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->unsignedBigInteger('attachment_size')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('live_chat_messages')) {
            return;
        }

        if (Schema::hasColumn('live_chat_messages', 'attachment_size')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->dropColumn('attachment_size');
            });
        }

        if (Schema::hasColumn('live_chat_messages', 'attachment_mime')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->dropColumn('attachment_mime');
            });
        }

        if (Schema::hasColumn('live_chat_messages', 'attachment_name')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->dropColumn('attachment_name');
            });
        }

        if (Schema::hasColumn('live_chat_messages', 'attachment_path')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->dropColumn('attachment_path');
            });
        }

        if (Schema::hasColumn('live_chat_messages', 'type')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->dropColumn('type');
            });
        }

        if (Schema::hasColumn('live_chat_messages', 'sender_user_id')) {
            Schema::table('live_chat_messages', function (Blueprint $table): void {
                $table->dropColumn('sender_user_id');
            });
        }
    }
};