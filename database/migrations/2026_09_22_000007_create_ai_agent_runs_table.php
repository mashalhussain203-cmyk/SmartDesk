<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_agent_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->string('mode', 32)->default('auto');
            $table->string('status', 32)->default('completed');
            $table->boolean('used_web')->default(false);
            $table->boolean('used_code')->default(false);
            $table->json('tools')->nullable();
            $table->json('sources')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agent_runs');
    }
};
