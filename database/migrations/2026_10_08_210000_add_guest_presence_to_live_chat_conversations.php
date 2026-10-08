<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chat_conversations', function (Blueprint $table): void {
            $table->timestamp('visitor_last_seen_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('live_chat_conversations', function (Blueprint $table): void {
            $table->dropColumn('visitor_last_seen_at');
        });
    }
};
