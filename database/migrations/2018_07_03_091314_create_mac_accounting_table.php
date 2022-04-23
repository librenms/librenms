<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMacAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mac_accounting', function (Blueprint $table) {
            $table->increments('ma_id');
            $table->unsignedInteger('port_id')->index();
            $table->string('mac', 32);
            $table->string('in_oid', 128);
            $table->string('out_oid', 128);
            $table->integer('bps_out');
            $table->integer('bps_in');
            $table->bigInteger('cipMacHCSwitchedBytes_input')->nullable();
            $table->bigInteger('cipMacHCSwitchedBytes_input_prev')->nullable();
            $table->bigInteger('cipMacHCSwitchedBytes_input_delta')->nullable();
            $table->integer('cipMacHCSwitchedBytes_input_rate')->nullable();
            $table->bigInteger('cipMacHCSwitchedBytes_output')->nullable();
            $table->bigInteger('cipMacHCSwitchedBytes_output_prev')->nullable();
            $table->bigInteger('cipMacHCSwitchedBytes_output_delta')->nullable();
            $table->integer('cipMacHCSwitchedBytes_output_rate')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_input')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_input_prev')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_input_delta')->nullable();
            $table->integer('cipMacHCSwitchedPkts_input_rate')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_output')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_output_prev')->nullable();
            $table->bigInteger('cipMacHCSwitchedPkts_output_delta')->nullable();
            $table->integer('cipMacHCSwitchedPkts_output_rate')->nullable();
            $table->unsignedInteger('poll_time')->nullable();
            $table->unsignedInteger('poll_prev')->nullable();
            $table->unsignedInteger('poll_period')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mac_accounting');
    }
}
