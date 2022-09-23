<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNetscalerVserversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('netscaler_vservers', function (Blueprint $table) {
            $table->increments('vsvr_id');
            $table->unsignedInteger('device_id');
            $table->string('vsvr_name', 128);
            $table->string('vsvr_ip', 128);
            $table->integer('vsvr_port');
            $table->string('vsvr_type', 64);
            $table->string('vsvr_state', 32);
            $table->integer('vsvr_clients');
            $table->integer('vsvr_server');
            $table->integer('vsvr_req_rate');
            $table->integer('vsvr_bps_in');
            $table->integer('vsvr_bps_out');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('netscaler_vservers');
    }
}
