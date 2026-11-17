<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // enable_vrfs is replaced by the vrf discovery module setting
        $enabled = DB::table('config')->where('config_name', 'enable_vrfs')->value('config_value');
        if ($enabled !== null && json_decode((string) $enabled) === false) {
            DB::table('config')->updateOrInsert(['config_name' => 'discovery_modules.vrf'], ['config_value' => 'false']);
        }

        // cisco-vrf-lite is now part of the vrf module
        DB::table('config')->whereIn('config_name', ['enable_vrfs', 'enable_vrf_lite_cisco'])->delete();
        DB::table('config')->where('config_name', 'like', '%discovery_modules.cisco-vrf-lite')->delete();
        DB::table('devices_attribs')->where('attrib_type', 'discover_cisco-vrf-lite')->delete();
    }

    public function down(): void
    {
        //
    }
};
