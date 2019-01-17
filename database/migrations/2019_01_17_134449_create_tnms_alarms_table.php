<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTnmsAlarmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tnmsalarms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('alarm_num');
            $table->unsignedInteger('device_id')->index('device_id');
            $table->integer('neID')->index('neID');
            $table->string('neAlarmtimestamp', 255);
            $table->string('alarm_sev', 128);
            $table->string('alarm_cause', 128);
            $table->string('alarm_location', 128);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tnmsalarms');
    }
}
