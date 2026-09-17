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
        Schema::create('email_login_links', function (Blueprint $table) {
            $table->id();

            $table
                ->string('email')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Token hash
            |--------------------------------------------------------------------------
            |
            | De echte token wordt nooit leesbaar in de database opgeslagen.
            |
            */

            $table->string('token_hash', 64)->unique();

            /*
            |--------------------------------------------------------------------------
            | Geldigheid
            |--------------------------------------------------------------------------
            */

            $table->timestamp('expires_at');

            /*
            |--------------------------------------------------------------------------
            | Eenmalig gebruik
            |--------------------------------------------------------------------------
            */

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
        Schema::dropIfExists('email_login_links');
    }
};