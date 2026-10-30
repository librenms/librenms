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
        // drop the type override on devices using a removed type, discovery then sets the os default type
        DB::table('devices_attribs')
            ->where('attrib_type', 'override_device_type')
            ->whereIn('device_id', DB::table('devices')
                ->whereIn('type', ['appliance', 'encoder', 'environmental', 'proxy', 'sensor'])
                ->select('device_id'))
            ->delete();
    }
};
