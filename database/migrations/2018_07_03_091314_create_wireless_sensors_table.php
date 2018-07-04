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
            $table->integer('sensor_id', true);
            $table->boolean('sensor_deleted')->default(0);
            $table->string('sensor_class', 64)->index('sensor_class');
            $table->integer('device_id')->unsigned()->default(0)->index('sensor_host');
            $table->string('sensor_index', 64)->nullable();
            $table->string('sensor_type')->index('sensor_type');
            $table->string('sensor_descr')->nullable();
            $table->integer('sensor_divisor')->default(1);
            $table->integer('sensor_multiplier')->default(1);
            $table->string('sensor_aggregator', 16)->default('sum');
            $table->float('sensor_current', 10, 0)->nullable();
            $table->float('sensor_prev', 10, 0)->nullable();
            $table->float('sensor_limit', 10, 0)->nullable();
            $table->float('sensor_limit_warn', 10, 0)->nullable();
            $table->float('sensor_limit_low', 10, 0)->nullable();
            $table->float('sensor_limit_low_warn', 10, 0)->nullable();
            $table->boolean('sensor_alert')->default(1);
            $table->enum('sensor_custom', array('No','Yes'))->default('No');
            $table->string('entPhysicalIndex', 16)->nullable();
            $table->string('entPhysicalIndex_measured', 16)->nullable();
            $table->timestamp('lastupdate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('sensor_oids', 65535);
            $table->integer('access_point_id')->nullable();
        });

        \DB::statement("ALTER TABLE `wireless_sensors` CHANGE `device_id` `device_id` int(11) unsigned NOT NULL DEFAULT '0' ;");
        \DB::statement("ALTER TABLE `wireless_sensors` CHANGE `sensor_current` `sensor_current` float NULL ;");
        \DB::statement("ALTER TABLE `wireless_sensors` CHANGE `sensor_prev` `sensor_prev` float NULL ;");
        \DB::statement("ALTER TABLE `wireless_sensors` CHANGE `sensor_limit` `sensor_limit` float NULL ;");
        \DB::statement("ALTER TABLE `wireless_sensors` CHANGE `sensor_limit_warn` `sensor_limit_warn` float NULL ;");
        \DB::statement("ALTER TABLE `wireless_sensors` CHANGE `sensor_limit_low` `sensor_limit_low` float NULL ;");
        \DB::statement("ALTER TABLE `wireless_sensors` CHANGE `sensor_limit_low_warn` `sensor_limit_low_warn` float NULL ;");
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
