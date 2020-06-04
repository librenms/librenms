<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertTransportTimerangeMapto extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_transport_timerange_map', function (Blueprint $table) {
            $table->increments('item_id');
            $table->unsignedInteger('transport_id')->index('transport_id');
            $table->unsignedInteger('mapto_id');
            $table->string('transport_mapto_type');
            $table->index(['transport_mapto_type', 'mapto_id'], 'schedulable_morph_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_transport_timerange_map');
    }
}
