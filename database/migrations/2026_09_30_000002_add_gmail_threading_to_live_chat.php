<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'live_chat_conversations',
            function (Blueprint $table): void {
                $table->string(
                    'gmail_thread_id',
                    191
                )
                    ->nullable()
                    ->index();

                $table->string(
                    'email_subject',
                    255
                )
                    ->nullable();
            }
        );

        Schema::create(
            'live_chat_settings',
            function (Blueprint $table): void {
                $table->string('key', 120)
                    ->primary();

                $table->text('value');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'live_chat_settings'
        );

        Schema::table(
            'live_chat_conversations',
            function (Blueprint $table): void {
                $table->dropIndex([
                    'gmail_thread_id',
                ]);

                $table->dropColumn([
                    'gmail_thread_id',
                    'email_subject',
                ]);
            }
        );
    }
};
