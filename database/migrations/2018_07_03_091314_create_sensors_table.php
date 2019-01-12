<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSensorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->increments('sensor_id');
            $table->boolean('sensor_deleted')->default(0);
            $table->string('sensor_class', 64)->index('sensor_class');
            $table->unsignedInteger('device_id')->default(0)->index('sensor_host');
            $table->string('poller_type', 16)->default('snmp');
            $table->string('sensor_oid');
            $table->string('sensor_index', 128)->nullable();
            $table->string('sensor_type')->index('sensor_type');
            $table->string('sensor_descr')->nullable();
            $table->string('group')->nullable();
            $table->bigInteger('sensor_divisor')->default(1);
            $table->integer('sensor_multiplier')->default(1);
            $table->double('sensor_current')->nullable();
            $table->double('sensor_limit')->nullable();
            $table->double('sensor_limit_warn')->nullable();
            $table->double('sensor_limit_low')->nullable();
            $table->double('sensor_limit_low_warn')->nullable();
            $table->boolean('sensor_alert')->default(1);
            $table->enum('sensor_custom', array('No','Yes'))->default('No');
            $table->string('entPhysicalIndex', 16)->nullable();
            $table->string('entPhysicalIndex_measured', 16)->nullable();
            $table->timestamp('lastupdate')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->double('sensor_prev')->nullable();
            $table->string('user_func', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sensors');
    }
}
