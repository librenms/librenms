<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddConstToSensors extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->integer('sensor_offset')->nullable();
        });
        Schema::table('wireless_sensors', function (Blueprint $table) {
            $table->integer('sensor_offset')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropColumn('sensor_offset');
        });
        Schema::table('wireless_sensors', function (Blueprint $table) {
            $table->dropColumn('sensor_offset');
        });
    }
}
