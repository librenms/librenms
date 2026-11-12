<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\MaintenanceBehavior;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // behavior is cast to MaintenanceBehavior, reset anything invalid to the column default
        DB::table('alert_schedule')
            ->whereNotIn('behavior', array_column(MaintenanceBehavior::cases(), 'value'))
            ->update(['behavior' => MaintenanceBehavior::SkipAlerts->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
