<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrateDevicesAttribsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->boolean('disable_notify')->default(0);
        });

        // migrate disable_notify data into devices table
        $devices = DB::table('devices_attribs')->where('attrib_type', 'disable_notify')->where('attrib_value', 1)->pluck('device_id');
        DB::table('devices')->whereIn('device_id', $devices)->update(['disable_notify' => 1]);
        DB::table('devices_attribs')->where('attrib_type', 'disable_notify')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // revert migrate disable_notify data into devices table
        $attribs = DB::table('devices')->where('disable_notify', 1)->pluck('device_id')->map(function ($device_id) {
            return [
                'device_id' => $device_id,
                'attrib_type' => 'disable_notify',
                'attrib_value' => 1,
            ];
        });
        DB::table('device_attribs')->insert($attribs->all());

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('disable_notify');
        });
    }
}
