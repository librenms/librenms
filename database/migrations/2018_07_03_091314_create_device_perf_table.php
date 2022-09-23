<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDevicePerfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_perf', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id')->index();
            $table->dateTime('timestamp');
            $table->integer('xmt');
            $table->integer('rcv');
            $table->integer('loss');
            $table->float('min');
            $table->float('max');
            $table->float('avg');
            $table->text('debug')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('device_perf');
    }
}
