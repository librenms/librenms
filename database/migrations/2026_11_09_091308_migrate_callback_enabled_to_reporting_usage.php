<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $enabled = DB::table('callback')->where('name', 'enabled')->value('value');
        if ($enabled === null) {
            return;
        }

        if (! DB::table('config')->where('config_name', 'reporting.usage')->exists()) {
            DB::table('config')->insert([
                'config_name' => 'reporting.usage',
                'config_value' => json_encode($enabled == 1),
            ]);
        }

        DB::table('callback')->where('name', 'enabled')->delete();
    }
};
