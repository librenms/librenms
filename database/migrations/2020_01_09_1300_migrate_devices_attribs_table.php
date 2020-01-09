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
        \DB::statement("UPDATE devices d, devices_attribs da SET d.disable_notify=1  WHERE da.attrib_type='disable_notify' AND da.attrib_value=1 AND d.device_id = da.device_id;");
        \DB::statement("DELETE FROM devices_attribs WHERE attrib_type='disable_notify' AND attrib_value=1;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // revert migrate disable_notify data into devices table
        \DB::statement("INSERT INTO devices_attribs (device_id, attrib_type, attrib_value) SELECT DISTINCT d.device_id, 'disable_notify', 1 FROM devices_attribs da INNER JOIN devices d WHERE d.device_id = da.device_id AND d.disable_notify=1;");
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('disable_notify');
        });
    }
}
