<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin', 'cms_admin', 'service_customer'])
                ->default('user')
                ->change();
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'service_customer')
            ->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin', 'cms_admin'])
                ->default('user')
                ->change();
        });
    }
};
