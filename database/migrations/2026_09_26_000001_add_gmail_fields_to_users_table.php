<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gmail-koppeling toevoegen aan de users-tabel.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Gmail OAuth tokens
            |--------------------------------------------------------------------------
            |
            | Deze waarden worden door GmailController versleuteld opgeslagen
            | met Laravel Crypt voordat ze in de database terechtkomen.
            |
            */

            $table
                ->text('google_access_token')
                ->nullable();

            $table
                ->text('google_refresh_token')
                ->nullable();

            $table
                ->timestamp('google_token_expires_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Gekoppeld Gmail-account
            |--------------------------------------------------------------------------
            |
            | Dit is het Gmail-adres dat daadwerkelijk toestemming heeft
            | gegeven aan Mashal Mail.
            |
            | GmailController controleert dat dit exact hetzelfde adres is
            | als het e-mailadres van het ingelogde Mashal-account.
            |
            */

            $table
                ->string('google_gmail_email')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Koppelingsdatum
            |--------------------------------------------------------------------------
            */

            $table
                ->timestamp('gmail_connected_at')
                ->nullable();
        });
    }

    /**
     * Gmail-koppeling verwijderen.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_access_token',
                'google_refresh_token',
                'google_token_expires_at',
                'google_gmail_email',
                'gmail_connected_at',
            ]);
        });
    }
};