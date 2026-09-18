<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        if (! Schema::hasColumn('favorites', 'user_id')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')
                    ->nullable()
                    ->index();
            });
        }

        if (! Schema::hasColumn('favorites', 'car_id')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unsignedBigInteger('car_id')
                    ->nullable()
                    ->index();
            });
        }
    }

    public function down(): void
    {
        //
        // Bewust leeg gelaten.
        // Deze migration repareert een bestaand schema en moet
        // geen bestaande productie-kolommen verwijderen.
        //
    }
};