<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertDeviceMapTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_device_map', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('rule_id');
            $table->integer('device_id');
            $table->unique(['rule_id','device_id'], 'alert_device_map_rule_id_device_id_uindex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_device_map');
    }
}
