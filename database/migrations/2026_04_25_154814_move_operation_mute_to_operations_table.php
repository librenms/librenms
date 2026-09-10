<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alert_operations', function (Blueprint $table): void {
            $table->boolean('notifications_suppressed')->default(false)->after('default_operation_step_duration_seconds');
        });

        Schema::table('alert_operation_segments', function (Blueprint $table): void {
            $table->dropColumn('notifications_suppressed');
        });
    }

    public function down(): void
    {
        Schema::table('alert_operation_segments', function (Blueprint $table): void {
            $table->boolean('notifications_suppressed')->default(false);
        });

        Schema::table('alert_operations', function (Blueprint $table): void {
            $table->dropColumn('notifications_suppressed');
        });
    }
};
