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
            $table->unsignedInteger('device_id');
            $table->bigInteger('going_down');
            $table->bigInteger('up_again')->nullable();
            $table->unique(['device_id', 'going_down']);
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
    }
}
