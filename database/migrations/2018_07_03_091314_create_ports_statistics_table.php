<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortsStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ports_statistics', function (Blueprint $table) {
            $table->unsignedInteger('port_id')->primary();
            $table->bigInteger('ifInNUcastPkts')->nullable();
            $table->bigInteger('ifInNUcastPkts_prev')->nullable();
            $table->bigInteger('ifInNUcastPkts_delta')->nullable();
            $table->integer('ifInNUcastPkts_rate')->nullable();
            $table->bigInteger('ifOutNUcastPkts')->nullable();
            $table->bigInteger('ifOutNUcastPkts_prev')->nullable();
            $table->bigInteger('ifOutNUcastPkts_delta')->nullable();
            $table->integer('ifOutNUcastPkts_rate')->nullable();
            $table->bigInteger('ifInDiscards')->nullable();
            $table->bigInteger('ifInDiscards_prev')->nullable();
            $table->bigInteger('ifInDiscards_delta')->nullable();
            $table->integer('ifInDiscards_rate')->nullable();
            $table->bigInteger('ifOutDiscards')->nullable();
            $table->bigInteger('ifOutDiscards_prev')->nullable();
            $table->bigInteger('ifOutDiscards_delta')->nullable();
            $table->integer('ifOutDiscards_rate')->nullable();
            $table->bigInteger('ifInUnknownProtos')->nullable();
            $table->bigInteger('ifInUnknownProtos_prev')->nullable();
            $table->bigInteger('ifInUnknownProtos_delta')->nullable();
            $table->integer('ifInUnknownProtos_rate')->nullable();
            $table->bigInteger('ifInBroadcastPkts')->nullable();
            $table->bigInteger('ifInBroadcastPkts_prev')->nullable();
            $table->bigInteger('ifInBroadcastPkts_delta')->nullable();
            $table->integer('ifInBroadcastPkts_rate')->nullable();
            $table->bigInteger('ifOutBroadcastPkts')->nullable();
            $table->bigInteger('ifOutBroadcastPkts_prev')->nullable();
            $table->bigInteger('ifOutBroadcastPkts_delta')->nullable();
            $table->integer('ifOutBroadcastPkts_rate')->nullable();
            $table->bigInteger('ifInMulticastPkts')->nullable();
            $table->bigInteger('ifInMulticastPkts_prev')->nullable();
            $table->bigInteger('ifInMulticastPkts_delta')->nullable();
            $table->integer('ifInMulticastPkts_rate')->nullable();
            $table->bigInteger('ifOutMulticastPkts')->nullable();
            $table->bigInteger('ifOutMulticastPkts_prev')->nullable();
            $table->bigInteger('ifOutMulticastPkts_delta')->nullable();
            $table->integer('ifOutMulticastPkts_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ports_statistics');
    }
}
