<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'pending_profile_changes')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('pending_profile_changes')->nullable();
            });
        }

        if (!Schema::hasColumn('drivers', 'pending_application_changes')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->json('pending_application_changes')->nullable();
            });
        }

        if (!Schema::hasColumn('vehicles', 'pending_application_changes')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->json('pending_application_changes')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'pending_profile_changes')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('pending_profile_changes');
            });
        }

        if (Schema::hasColumn('drivers', 'pending_application_changes')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('pending_application_changes');
            });
        }

        if (Schema::hasColumn('vehicles', 'pending_application_changes')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('pending_application_changes');
            });
        }
    }
};
