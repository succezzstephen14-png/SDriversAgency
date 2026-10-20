<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('guarantor_passport_file')->nullable()->after('guarantor_ghana_card_file');
            $table->string('second_guarantor_passport_file')->nullable()->after('second_guarantor_ghana_card_file');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'guarantor_passport_file',
                'second_guarantor_passport_file',
            ]);
        });
    }
};
