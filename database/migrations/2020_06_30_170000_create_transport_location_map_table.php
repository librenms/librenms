<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTransportLocationMapTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_location_map', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transport_id');
            $table->unsignedInteger('location_id');
            $table->unique(['transport_id','location_id'], 'transport_location_map_transport_id_location_id_uindex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transport_location_map');
    }
}
