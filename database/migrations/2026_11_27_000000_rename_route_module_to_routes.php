<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename the route discovery module to routes in global, per-os, and per-device settings.
     * Reads the loaded config so values set in config.php are carried over too.
     */
    public function up(): void
    {
        $settings = [];

        if (LibrenmsConfig::has('discovery_modules.route')) {
            $settings['discovery_modules.routes'] = LibrenmsConfig::get('discovery_modules.route');
        }

        foreach ((array) LibrenmsConfig::get('os', []) as $os => $osConfig) {
            if (is_array($osConfig) && isset($osConfig['discovery_modules']['route'])) {
                $settings["os.$os.discovery_modules.routes"] = $osConfig['discovery_modules']['route'];
            }
        }

        foreach ($settings as $name => $value) {
            DB::table('config')->updateOrInsert(['config_name' => $name], ['config_value' => json_encode($value)]);
        }

        DB::table('config')->where('config_name', 'discovery_modules.route')->delete();
        DB::table('config')->where('config_name', 'like', 'os.%.discovery_modules.route')->delete();

        DB::table('devices_attribs')->where('attrib_type', 'discover_route')->update(['attrib_type' => 'discover_routes']);
    }
};
