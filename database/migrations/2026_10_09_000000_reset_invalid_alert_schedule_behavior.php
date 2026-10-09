<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // behavior is cast to MaintenanceBehavior (1-3), reset anything else to the column default (skip alerts)
        DB::table('alert_schedule')->whereNotIn('behavior', [1, 2, 3])->update(['behavior' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
