<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_activities', function (Blueprint $table) {
            $table->id();
            $table->string('assignable_type');
            $table->string('assignable_id');
            $table->string('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->enum('entry_type', [
                'progress',
                'customer_report',
                'driver_report',
                'driver_assessment',
                'admin_feedback',
            ]);
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('message');
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id', 'created_at'], 'assignment_activities_target_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_activities');
    }
};
