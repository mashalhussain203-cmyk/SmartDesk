<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg Microsoft OAuth-ondersteuning toe aan gebruikers.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('microsoft_id', 128)
                ->nullable()
                ->unique('users_microsoft_id_unique');
        });
    }

    /**
     * Draai de Microsoft OAuth-wijziging terug.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_microsoft_id_unique');
            $table->dropColumn('microsoft_id');
        });
    }
};
