<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEntityStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entityState', function (Blueprint $table) {
            $table->increments('entity_state_id');
            $table->unsignedInteger('device_id')->nullable()->index();
            $table->unsignedInteger('entPhysical_id')->nullable();
            $table->dateTime('entStateLastChanged')->nullable();
            $table->integer('entStateAdmin')->nullable();
            $table->integer('entStateOper')->nullable();
            $table->integer('entStateUsage')->nullable();
            $table->text('entStateAlarm')->nullable();
            $table->integer('entStateStandby')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('entityState');
    }
}
