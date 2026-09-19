<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg uitgebreide login-securityvelden toe.
     */
    public function up(): void
    {
        Schema::table('login_activities', function (Blueprint $table): void {

            /*
            |--------------------------------------------------------------------------
            | Browser timezone
            |--------------------------------------------------------------------------
            |
            | Bijvoorbeeld:
            |
            | Europe/Amsterdam
            | Asia/Karachi
            | America/New_York
            |
            */

            $table->string(
                'browser_timezone',
                100
            )
                ->nullable()
                ->after('location_source');


            /*
            |--------------------------------------------------------------------------
            | Precieze browserlocatie
            |--------------------------------------------------------------------------
            |
            | Alleen gevuld wanneer de gebruiker expliciet
            | browserlocatie heeft toegestaan.
            |
            */

            $table->decimal(
                'latitude',
                10,
                7
            )
                ->nullable()
                ->after('browser_timezone');

            $table->decimal(
                'longitude',
                10,
                7
            )
                ->nullable()
                ->after('latitude');

            /*
            |--------------------------------------------------------------------------
            | GPS-nauwkeurigheid
            |--------------------------------------------------------------------------
            |
            | In meters.
            |
            | Bijvoorbeeld:
            |
            | 8.50
            | 25.00
            | 120.75
            |
            */

            $table->decimal(
                'location_accuracy',
                10,
                2
            )
                ->nullable()
                ->after('longitude');


            /*
            |--------------------------------------------------------------------------
            | Browser locatietoestemming
            |--------------------------------------------------------------------------
            |
            | Mogelijke waarden:
            |
            | granted
            | denied
            | prompt
            | unavailable
            | unsupported
            | unknown
            |
            */

            $table->string(
                'location_permission',
                20
            )
                ->nullable()
                ->after('location_accuracy');


            /*
            |--------------------------------------------------------------------------
            | Wanneer GPS werkelijk werd vastgelegd
            |--------------------------------------------------------------------------
            */

            $table->timestamp(
                'precise_location_captured_at'
            )
                ->nullable()
                ->after('location_permission');


            /*
            |--------------------------------------------------------------------------
            | Beveiligingsmail status
            |--------------------------------------------------------------------------
            */

            $table->timestamp(
                'notification_sent_at'
            )
                ->nullable()
                ->after('logged_in_at');

            $table->timestamp(
                'notification_failed_at'
            )
                ->nullable()
                ->after('notification_sent_at');
        });
    }

    /**
     * Verwijder uitsluitend de velden van deze migration.
     */
    public function down(): void
    {
        Schema::table('login_activities', function (Blueprint $table): void {
            $table->dropColumn([
                'browser_timezone',
                'latitude',
                'longitude',
                'location_accuracy',
                'location_permission',
                'precise_location_captured_at',
                'notification_sent_at',
                'notification_failed_at',
            ]);
        });
    }
};