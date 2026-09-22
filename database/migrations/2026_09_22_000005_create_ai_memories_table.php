<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_memories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 120)->nullable();
            $table->text('content');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'enabled', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_memories');
    }
};
