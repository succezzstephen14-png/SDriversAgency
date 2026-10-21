<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('request_type', ['driver', 'vehicle']);
            $table->string('service_type');
            $table->string('vehicle_type')->nullable();
            $table->string('location');
            $table->date('preferred_start_date')->nullable();
            $table->string('duration')->nullable();
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'awaiting_payment', 'paid', 'rejected', 'completed'])->default('pending');
            $table->string('assigned_driver_id')->nullable();
            $table->foreign('assigned_driver_id')->references('id')->on('drivers')->nullOnDelete();
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->unsignedInteger('quoted_amount')->nullable();
            $table->text('admin_message')->nullable();
            $table->string('payment_reference')->nullable()->unique();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['request_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
