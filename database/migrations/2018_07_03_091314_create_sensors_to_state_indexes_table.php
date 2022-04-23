<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSensorsToStateIndexesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensors_to_state_indexes', function (Blueprint $table) {
            $table->increments('sensors_to_state_translations_id');
            $table->unsignedInteger('sensor_id');
            $table->unsignedInteger('state_index_id')->index();
            $table->unique(['sensor_id', 'state_index_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sensors_to_state_indexes');
    }
}
