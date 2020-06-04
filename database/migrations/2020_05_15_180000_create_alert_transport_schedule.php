<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertTransportSchedule extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_transport_schedule', function (Blueprint $table) {
            $table->increments('transport_schedule_id');
            $table->unsignedInteger('transport_id')->index('transport_id');
            $table->time('start_hr')->default('00:00:00');
            $table->time('end_hr')->default('00:00:00');
            $table->string('day', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_transport_schedule');
    }
}
