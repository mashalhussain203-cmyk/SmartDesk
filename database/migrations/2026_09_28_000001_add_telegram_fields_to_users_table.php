<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg Telegram-loginvelden toe aan de users-tabel.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('telegram_id', 128)
                ->nullable()
                ->unique('users_telegram_id_unique');

            $table->string('telegram_username', 64)
                ->nullable();

            $table->text('telegram_avatar')
                ->nullable();
        });
    }

    /**
     * Draai de Telegram-loginvelden terug.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_telegram_id_unique');

            $table->dropColumn([
                'telegram_id',
                'telegram_username',
                'telegram_avatar',
            ]);
        });
    }
};