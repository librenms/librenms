<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // cleanup duplicates
        $plugins = DB::table('plugins')->groupBy(['version', 'plugin_name'])->select(['version', 'plugin_name'])->get();
        $valid_plugins = [];
        foreach ($plugins as $plugin) {
            // find the newest id with settings
            $valid_plugins[] = DB::table('plugins')
                ->where(['version' => $plugin->version, 'plugin_name' => $plugin->plugin_name])
                ->orderBy('settings', 'DESC')
                ->orderBy('plugin_id', 'DESC')
                ->value('plugin_id');
        }
        DB::table('plugins')->whereNotIn('plugin_id', $valid_plugins)->delete();

        Schema::table('plugins', function (Blueprint $table) {
            $table->unique(['version', 'plugin_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropUnique('plugins_version_plugin_name_unique');
        });
    }
};
