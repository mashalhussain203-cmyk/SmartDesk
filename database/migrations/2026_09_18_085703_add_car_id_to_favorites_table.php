<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de migration uit.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Favorites-tabel bestaat nog niet
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasTable('favorites')) {
            Schema::create('favorites', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')
                    ->nullable()
                    ->index();

                $table->unsignedBigInteger('car_id')
                    ->nullable()
                    ->index();

                $table->timestamps();
            });

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | user_id toevoegen indien deze ontbreekt
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('favorites', 'user_id')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')
                    ->nullable()
                    ->index();
            });
        }


        /*
        |--------------------------------------------------------------------------
        | car_id toevoegen indien deze ontbreekt
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('favorites', 'car_id')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unsignedBigInteger('car_id')
                    ->nullable()
                    ->index();
            });
        }
    }


    /**
     * Draai de migration terug.
     *
     * Bewust leeg:
     * deze migration repareert een bestaande productie-database.
     * We willen bij rollback geen bestaande favorietenkolommen verwijderen.
     */
    public function down(): void
    {
        //
    }
};