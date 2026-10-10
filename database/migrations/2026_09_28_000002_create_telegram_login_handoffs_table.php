<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('telegram_login_handoffs')) {
            return;
        }

        Schema::create('telegram_login_handoffs', function (Blueprint $table) {
            $table->id();

            $table
                ->char('token_hash', 64)
                ->unique();

            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table
                ->json('telegram_data')
                ->nullable();

            $table
                ->string('intended_url', 2048)
                ->nullable();

            $table
                ->timestamp('expires_at')
                ->index();

            $table
                ->timestamp('used_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_login_handoffs');
    }
};
