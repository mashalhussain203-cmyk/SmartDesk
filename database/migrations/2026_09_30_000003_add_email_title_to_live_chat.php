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
                $table->string('email_title', 120)
                    ->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'live_chat_conversations',
            function (Blueprint $table): void {
                $table->dropColumn('email_title');
            }
        );
    }
};
