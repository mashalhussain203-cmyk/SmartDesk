<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('image_id')
                ->constrained('images')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('file_name');

            $table->string('path');

            $table->string('format', 20);

            $table->unsignedInteger('width');

            $table->unsignedInteger('height');

            $table->unsignedTinyInteger('quality')
                ->default(88);

            $table->unsignedBigInteger('file_size')
                ->default(0);

            $table->string('operation', 40);

            $table->timestamps();

            $table->index([
                'image_id',
                'created_at',
            ]);

            $table->index([
                'user_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_versions');
    }
};
