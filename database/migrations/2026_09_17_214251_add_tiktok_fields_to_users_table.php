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
        Schema::table('users', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | TikTok OAuth
            |--------------------------------------------------------------------------
            |
            | tiktok_id:
            | De unieke TikTok Open ID van de gebruiker.
            |
            | tiktok_avatar:
            | De avatar-URL die via TikTok Login Kit / user.info.basic
            | wordt ontvangen.
            |
            */

            $table->string('tiktok_id')
                ->nullable()
                ->unique();

            $table->text('tiktok_avatar')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | TikTok OAuth verwijderen
            |--------------------------------------------------------------------------
            */

            $table->dropUnique([
                'tiktok_id',
            ]);

            $table->dropColumn([
                'tiktok_id',
                'tiktok_avatar',
            ]);
        });
    }
};