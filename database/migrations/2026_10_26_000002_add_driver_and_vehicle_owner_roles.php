<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'driver', 'vehicle_owner', 'admin', 'cms_admin', 'service_customer'])
                ->default('user')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('role', ['driver', 'vehicle_owner'])
            ->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin', 'cms_admin', 'service_customer'])
                ->default('user')
                ->change();
        });
    }
};
