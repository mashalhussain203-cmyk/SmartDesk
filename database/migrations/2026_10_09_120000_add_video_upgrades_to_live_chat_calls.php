<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chat_calls', function (Blueprint $table): void {
            $table->string('video_upgrade_status', 24)->nullable();
            $table->string('video_upgrade_requested_by', 12)->nullable();
            $table->unsignedInteger('video_upgrade_version')->default(0);
            $table->longText('video_upgrade_offer_json')->nullable();
            $table->longText('video_upgrade_answer_json')->nullable();
            $table->timestamp('video_upgrade_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('live_chat_calls', function (Blueprint $table): void {
            $table->dropColumn([
                'video_upgrade_status',
                'video_upgrade_requested_by',
                'video_upgrade_version',
                'video_upgrade_offer_json',
                'video_upgrade_answer_json',
                'video_upgrade_requested_at',
            ]);
        });
    }
};
