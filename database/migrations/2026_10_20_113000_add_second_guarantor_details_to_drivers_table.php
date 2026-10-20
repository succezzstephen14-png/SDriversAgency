<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('second_guarantor')->nullable()->after('relationship');
            $table->string('second_guarantor_phone')->nullable()->after('second_guarantor');
            $table->string('second_g_ghana_card_no')->nullable()->after('second_guarantor_phone');
            $table->string('second_guarantor_ghana_card_file')->nullable()->after('second_g_ghana_card_no');
            $table->string('second_relationship')->nullable()->after('second_guarantor_ghana_card_file');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'second_guarantor',
                'second_guarantor_phone',
                'second_g_ghana_card_no',
                'second_guarantor_ghana_card_file',
                'second_relationship',
            ]);
        });
    }
};
