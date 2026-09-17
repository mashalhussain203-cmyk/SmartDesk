<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'facebook_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('facebook_id', 255)->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'facebook_avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('facebook_avatar')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'facebook_avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('facebook_avatar');
            });
        }

        if (Schema::hasColumn('users', 'facebook_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('facebook_id');
            });
        }
    }
};