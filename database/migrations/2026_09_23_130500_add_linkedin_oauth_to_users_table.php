<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'linkedin_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table
                    ->string('linkedin_id')
                    ->nullable()
                    ->unique()
                    ->after('tiktok_avatar');
            });
        }

        if (! Schema::hasColumn('users', 'linkedin_avatar')) {
            Schema::table('users', function (Blueprint $table): void {
                $table
                    ->text('linkedin_avatar')
                    ->nullable()
                    ->after('linkedin_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'linkedin_avatar')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('linkedin_avatar');
            });
        }

        if (Schema::hasColumn('users', 'linkedin_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique('users_linkedin_id_unique');
                $table->dropColumn('linkedin_id');
            });
        }
    }
};
