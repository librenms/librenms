<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLoadbalancerRserversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loadbalancer_rservers', function (Blueprint $table) {
            $table->increments('rserver_id');
            $table->string('farm_id', 128);
            $table->unsignedInteger('device_id');
            $table->string('StateDescr', 64);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('loadbalancer_rservers');
    }
}
