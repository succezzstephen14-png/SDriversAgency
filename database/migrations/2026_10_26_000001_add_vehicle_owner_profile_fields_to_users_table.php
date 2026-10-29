<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable();
            $table->string('ghana_card_number', 30)->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('ghana_card_picture')->nullable();
            $table->string('digital_address')->nullable();
            $table->string('guarantor_name')->nullable();
            $table->string('guarantor_contact', 20)->nullable();
            $table->string('guarantor_ghana_card_picture')->nullable();
            $table->string('guarantor_relationship', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'ghana_card_number',
                'profile_picture',
                'ghana_card_picture',
                'digital_address',
                'guarantor_name',
                'guarantor_contact',
                'guarantor_ghana_card_picture',
                'guarantor_relationship',
            ]);
        });
    }
};
