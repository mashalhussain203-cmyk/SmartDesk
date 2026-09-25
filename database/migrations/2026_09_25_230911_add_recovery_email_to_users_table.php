<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasColumn(
                'users',
                'recovery_email'
            )
        ) {
            Schema::table(
                'users',
                function (Blueprint $table): void {
                    $table
                        ->string('recovery_email')
                        ->nullable()
                        ->after('email');

                    $table->index(
                        'recovery_email',
                        'users_recovery_email_index'
                    );
                }
            );
        }

        if (
            ! Schema::hasColumn(
                'users',
                'recovery_email_verified_at'
            )
        ) {
            Schema::table(
                'users',
                function (Blueprint $table): void {
                    $table
                        ->timestamp(
                            'recovery_email_verified_at'
                        )
                        ->nullable()
                        ->after('recovery_email');
                }
            );
        }
    }

    public function down(): void
    {
        /*
         * recovery_email kan uit een eerdere migration komen.
         * Daarom verwijderen we hier alleen het verificatie-tijdstip.
         */
        if (
            Schema::hasColumn(
                'users',
                'recovery_email_verified_at'
            )
        ) {
            Schema::table(
                'users',
                function (Blueprint $table): void {
                    $table->dropColumn(
                        'recovery_email_verified_at'
                    );
                }
            );
        }
    }
};
