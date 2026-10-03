<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_chat_calls', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->enum('initiated_by', ['visitor', 'admin']);
            $table->unsignedBigInteger('admin_user_id')->nullable()->index();
            $table->enum('mode', ['audio', 'video']);
            $table->enum('status', [
                'ringing',
                'accepted',
                'declined',
                'ended',
                'missed',
                'failed',
            ])->default('ringing')->index();
            $table->longText('offer_json')->nullable();
            $table->longText('answer_json')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('live_chat_conversations')
                ->cascadeOnDelete();

            $table->foreign('admin_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_chat_calls');
    }
};
