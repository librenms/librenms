<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // check for existence if migration fails
        if (! Schema::hasColumn('custom_maps', 'background_data')) {
            Schema::table('custom_maps', function (Blueprint $table) {
                $table->string('background_type', 16)->default('none');
                $table->text('background_data')->nullable();
            });
        }

        // migrate data
        DB::table('custom_maps')->select(['custom_map_id', 'background_suffix', 'background_version'])->get()->map(function ($map) {
            if ($map->background_suffix) {
                DB::table('custom_maps')->where('custom_map_id', $map->custom_map_id)->update([
                    'background_type' => 'image',
                    'background_data' => json_encode([
                        'suffix' => $map->background_suffix,
                        'version' => $map->background_version,
                    ]),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // migrate data
        DB::table('custom_maps')->select(['custom_map_id', 'background_type', 'background_data'])->get()->map(function ($map) {
            if ($map->background_type == 'image' && $map->background_data) {
                $data = json_decode($map->background_data, true);
                DB::table('custom_maps')->where('custom_map_id', $map->custom_map_id)->update([
                    'background_suffix' => $data['suffix'],
                    'background_version' => $data['version'],
                ]);
            }
        });

        Schema::table('custom_maps', function (Blueprint $table) {
            $table->dropColumn(['background_type', 'background_data']);
        });
    }
};
