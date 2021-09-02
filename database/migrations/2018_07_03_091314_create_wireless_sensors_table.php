<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWirelessSensorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wireless_sensors', function (Blueprint $table) {
            $table->increments('sensor_id');
            $table->boolean('sensor_deleted')->default(0);
            $table->string('sensor_class', 64)->index();
            $table->unsignedInteger('device_id')->default(0)->index();
            $table->string('sensor_index', 64)->nullable();
            $table->string('sensor_type')->index();
            $table->string('sensor_descr')->nullable();
            $table->integer('sensor_divisor')->default(1);
            $table->integer('sensor_multiplier')->default(1);
            $table->string('sensor_aggregator', 16)->default('sum');
            $table->double('sensor_current')->nullable();
            $table->double('sensor_prev')->nullable();
            $table->double('sensor_limit')->nullable();
            $table->double('sensor_limit_warn')->nullable();
            $table->double('sensor_limit_low')->nullable();
            $table->double('sensor_limit_low_warn')->nullable();
            $table->boolean('sensor_alert')->default(1);
            $table->enum('sensor_custom', ['No', 'Yes'])->default('No');
            $table->string('entPhysicalIndex', 16)->nullable();
            $table->string('entPhysicalIndex_measured', 16)->nullable();
            $table->timestamp('lastupdate')->useCurrent();
            $table->text('sensor_oids');
            $table->unsignedInteger('access_point_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wireless_sensors');
    }
}
