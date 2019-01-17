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
        Schema::table('tnmsneinfo', function (Blueprint $table) {
            $table->renameColumn('id', 'tnmsne_info_id');
        });
        Schema::create('tnms_alarms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tnmsne_info_id');
            $table->unsignedInteger('alarm_num');
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
        Schema::dropIfExists('tnms_alarms');
    }
}
