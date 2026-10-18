<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('tiktok_post_access_token')->nullable();
            $table->text('tiktok_post_refresh_token')->nullable();
            $table->string('tiktok_post_open_id', 150)->nullable();
            $table->string('tiktok_post_scopes', 255)->nullable();
            $table->timestamp('tiktok_post_expires_at')->nullable();
            $table->timestamp('tiktok_post_connected_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'tiktok_post_access_token',
                'tiktok_post_refresh_token',
                'tiktok_post_open_id',
                'tiktok_post_scopes',
                'tiktok_post_expires_at',
                'tiktok_post_connected_at',
            ]);
        });
    }
};
