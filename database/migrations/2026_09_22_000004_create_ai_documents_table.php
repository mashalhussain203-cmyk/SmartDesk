<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained('ai_projects')->nullOnDelete();
            $table->foreignUuid('conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->string('original_name', 255);
            $table->string('extension', 32)->nullable();
            $table->string('mime_type', 160)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('disk', 64)->default('local');
            $table->string('path', 1024);
            $table->string('analysis', 64)->nullable();
            $table->longText('extracted_text')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'project_id', 'created_at']);
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_documents');
    }
};
