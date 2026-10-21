<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['drivers' => 'DRV-APP', 'vehicles' => 'VEH-APP'] as $tableName => $prefix) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'application_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('application_id', 32)->nullable()->unique();
            });

            DB::table($tableName)
                ->select('id')
                ->orderBy('id')
                ->chunkById(100, function ($records) use ($tableName, $prefix): void {
                    foreach ($records as $record) {
                        do {
                            $applicationId = $prefix . '-' . strtoupper(Str::random(12));
                        } while (DB::table($tableName)->where('application_id', $applicationId)->exists());

                        DB::table($tableName)
                            ->where('id', $record->id)
                            ->update(['application_id' => $applicationId]);
                    }
                });
        }
    }

    public function down(): void
    {
        foreach (['drivers', 'vehicles'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'application_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropColumn('application_id');
                });
            }
        }
    }
};
