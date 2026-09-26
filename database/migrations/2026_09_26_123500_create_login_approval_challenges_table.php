<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'login_approval_challenges',
            function (Blueprint $table): void {
                $table
                    ->uuid('id')
                    ->primary();

                $table
                    ->unsignedBigInteger(
                        'user_id'
                    )
                    ->index();

                $table
                    ->string(
                        'origin_session_id',
                        255
                    )
                    ->index();

                $table
                    ->string(
                        'status',
                        20
                    )
                    ->default('pending')
                    ->index();

                $table->string(
                    'number_hash'
                );

                $table->json(
                    'options'
                );

                $table
                    ->boolean(
                        'remember'
                    )
                    ->default(false);

                $table
                    ->string(
                        'login_provider',
                        50
                    )
                    ->default('password');

                $table
                    ->string(
                        'requested_ip',
                        45
                    )
                    ->nullable();

                $table
                    ->text(
                        'requested_user_agent'
                    )
                    ->nullable();

                $table
                    ->string(
                        'approved_by_session_id',
                        255
                    )
                    ->nullable();

                $table
                    ->timestamp(
                        'approved_at'
                    )
                    ->nullable();

                $table
                    ->timestamp(
                        'expires_at'
                    )
                    ->index();

                $table
                    ->timestamp(
                        'consumed_at'
                    )
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'user_id',
                        'status',
                        'expires_at',
                    ],
                    'login_approval_user_status_expiry'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'login_approval_challenges'
        );
    }
};
