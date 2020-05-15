<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertSchedulablesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_transport_schedulables', function (Blueprint $table) {
            $table->increments('item_id');
            $table->unsignedInteger('transport_schedule_id')->index('transport_schedule_id');
            $table->unsignedInteger('transport_schedulable_id');
            $table->string('transport_schedulable_type');
            $table->index(['transport_schedulable_type', 'transport_schedulable_id'], 'schedulable_morph_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_transport_schedulables');
    }
}
