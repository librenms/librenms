<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTransportDeviceMapTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_device_map', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transport_id');
            $table->unsignedInteger('device_id');
            $table->unique(['transport_id','device_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transport_device_map');
    }
}
