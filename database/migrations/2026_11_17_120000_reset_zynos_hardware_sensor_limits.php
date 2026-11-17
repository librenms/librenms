<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep existing bounds active until discovery replaces and recalculates them.
        foreach (['fanspeed' => 1, 'temperature' => 2, 'voltage' => 3] as $class => $table) {
            DB::table('sensors')
                ->where('sensor_type', 'zynos')
                ->where('sensor_custom', 'No')
                ->where('sensor_class', $class)
                ->where('sensor_oid', 'like', '.1.3.6.1.4.1.890.1.15.3.26.1.' . $table . '.1.3.%')
                ->update(['sensor_custom' => 'Reset']);
        }
    }

    public function down(): void
    {
    }
};
