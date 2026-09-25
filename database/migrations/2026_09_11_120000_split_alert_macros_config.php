<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rawConfig = DB::table('config')->where('config_name', 'alert.macros.rule')->value('config_value');
        if ($rawConfig === null) {
            return;
        }

        $macros = json_decode((string) $rawConfig, true);
        if (! is_array($macros)) {
            return;
        }

        $definitions = LibrenmsConfig::getDefinitions();

        foreach ($macros as $macro => $value) {
            $setting = "alert.macros.rule.$macro";
            $default = $definitions[$setting]['default'] ?? null;
            if ($value !== $default) {
                DB::table('config')->updateOrInsert(
                    ['config_name' => $setting],
                    ['config_value' => json_encode($value, JSON_UNESCAPED_SLASHES)]
                );
            }
        }

        DB::table('config')->where('config_name', 'alert.macros.rule')->delete();
    }
};
