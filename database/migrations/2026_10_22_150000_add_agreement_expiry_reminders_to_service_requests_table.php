<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->date('agreement_end_date')->nullable();
            $table->unsignedTinyInteger('expiry_reminder_days')->nullable();
            $table->timestamp('expiry_reminder_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'agreement_end_date',
                'expiry_reminder_days',
                'expiry_reminder_sent_at',
            ]);
        });
    }
};
