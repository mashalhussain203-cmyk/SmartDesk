<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maak de loginactiviteiten-tabel.
     */
    public function up(): void
    {
        if (Schema::hasTable('login_activities')) {
            return;
        }

        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('guard', 50)
                ->default('web');

            $table->string('login_provider', 50)
                ->default('unknown');

            $table->string('ip_address', 45)
                ->nullable();

            $table->string('city', 120)
                ->nullable();

            $table->string('region', 120)
                ->nullable();

            $table->string('country', 120)
                ->nullable();

            $table->string('country_code', 10)
                ->nullable();

            $table->string('timezone', 100)
                ->nullable();

            $table->string('location_source', 50)
                ->nullable();

            $table->string('device', 190)
                ->nullable();

            $table->string('device_type', 30)
                ->nullable();

            $table->string('browser', 120)
                ->nullable();

            $table->string('operating_system', 120)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->char('device_fingerprint', 64)
                ->nullable();

            $table->boolean('is_new_device')
                ->default(false);

            $table->boolean('remember')
                ->default(false);

            $table->timestamp('logged_in_at')
                ->useCurrent();

            $table->timestamps();

            $table->index([
                'user_id',
                'logged_in_at',
            ]);

            $table->index([
                'user_id',
                'device_fingerprint',
            ]);
        });
    }

    /**
     * Verwijder de loginactiviteiten-tabel.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'login_activities'
        );
    }
};
