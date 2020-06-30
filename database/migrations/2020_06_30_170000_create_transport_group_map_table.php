<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTransportGroupMapTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_group_map', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transport_id');
            $table->unsignedInteger('group_id');
            $table->unique(['transport_id','group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transport_group_map');
    }
}
