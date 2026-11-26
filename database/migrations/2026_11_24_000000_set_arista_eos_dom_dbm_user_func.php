<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Arista EOS DOM power was converted to dBm by a polling include file.
     * The sensor row now holds the conversion, so set it on existing sensors.
     * Without this, they poll the raw value until the next discovery.
     */
    public function up(): void
    {
        DB::table('sensors')
            ->whereIn('device_id', DB::table('devices')->where('os', 'arista_eos')->select('device_id'))
            ->where('sensor_class', 'dbm')
            ->where('sensor_type', 'entity-sensor')
            ->whereNull('user_func')
            ->where(fn ($query) => $query->where('sensor_descr', 'like', '%DOM Rx Power%')->orWhere('sensor_descr', 'like', '%DOM Tx Power%'))
            ->update([
                'sensor_divisor' => 10,
                'sensor_multiplier' => 1,
                'user_func' => 'uw_to_dbm',
            ]);
    }

    public function down(): void
    {
        // The old polling include is gone, so the old values would poll wrong.
    }
};
