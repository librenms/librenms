<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeviceOutagesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_outages', function (Blueprint $table) {
            $table->unsignedInteger('device_id')->default(0)->index();
            $table->bigInteger('going_down');
            $table->bigInteger('up_again')->nullable();
            $table->unique(['device_id', 'going_down']);
        });
        Schema::create('availability', function (Blueprint $table) {
            $table->increments('availability_id');
            $table->unsignedInteger('device_id')->index();
            $table->bigInteger('duration');
            $table->float('availability_perc', 6, 6)->default(0);
            $table->unique(['device_id','duration']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('device_outages');
        Schema::drop('availability');
    }
}
