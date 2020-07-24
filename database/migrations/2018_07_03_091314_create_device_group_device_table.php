<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeviceGroupDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_group_device', function (Blueprint $table) {
            $table->unsignedInteger('device_group_id')->unsigned()->index();
            $table->unsignedInteger('device_id')->unsigned()->index();
            $table->primary(['device_group_id', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('device_group_device');
    }
}
