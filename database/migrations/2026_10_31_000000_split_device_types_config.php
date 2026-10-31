<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rawConfig = DB::table('config')->where('config_name', 'device_types')->value('config_value');
        if ($rawConfig === null) {
            return;
        }

        $types = json_decode((string) $rawConfig, true);
        if (is_array($types)) {
            $definitions = LibrenmsConfig::getDefinitions();
            $removed = ['appliance', 'camera', 'encoder', 'environmental', 'other', 'proxy', 'sensor'];

            foreach ($types as $type) {
                if (! isset($type['type'], $type['icon']) || in_array($type['type'], $removed, true)) {
                    continue;
                }

                $setting = "device_types.{$type['type']}";
                $default = $definitions[$setting]['default'] ?? null;
                if ($type['icon'] !== $default) {
                    DB::table('config')->updateOrInsert(
                        ['config_name' => $setting],
                        ['config_value' => json_encode($type['icon'], JSON_UNESCAPED_SLASHES)]
                    );
                }
            }
        }

        DB::table('config')->where('config_name', 'device_types')->delete();
    }
};
