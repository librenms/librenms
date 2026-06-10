<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ports_statistics', function (Blueprint $table) {
            $table->bigInteger('ifInNUcastPkts')->unsigned()->nullable()->change();
            $table->bigInteger('ifInNUcastPkts_prev')->unsigned()->nullable()->change();
            $table->bigInteger('ifOutNUcastPkts')->unsigned()->nullable()->change();
            $table->bigInteger('ifOutNUcastPkts_prev')->unsigned()->nullable()->change();
            $table->bigInteger('ifInDiscards')->unsigned()->nullable()->change();
            $table->bigInteger('ifInDiscards_prev')->unsigned()->nullable()->change();
            $table->bigInteger('ifOutDiscards')->unsigned()->nullable()->change();
            $table->bigInteger('ifOutDiscards_prev')->unsigned()->nullable()->change();
            $table->bigInteger('ifInUnknownProtos')->unsigned()->nullable()->change();
            $table->bigInteger('ifInUnknownProtos_prev')->unsigned()->nullable()->change();
            $table->bigInteger('ifInBroadcastPkts')->unsigned()->nullable()->change();
            $table->bigInteger('ifInBroadcastPkts_prev')->unsigned()->nullable()->change();
            $table->bigInteger('ifOutBroadcastPkts')->unsigned()->nullable()->change();
            $table->bigInteger('ifOutBroadcastPkts_prev')->unsigned()->nullable()->change();
            $table->bigInteger('ifInMulticastPkts')->unsigned()->nullable()->change();
            $table->bigInteger('ifInMulticastPkts_prev')->unsigned()->nullable()->change();
            $table->bigInteger('ifOutMulticastPkts')->unsigned()->nullable()->change();
            $table->bigInteger('ifOutMulticastPkts_prev')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ports_statistics', function (Blueprint $table) {
            $table->bigInteger('ifInNUcastPkts')->nullable()->change();
            $table->bigInteger('ifInNUcastPkts_prev')->nullable()->change();
            $table->bigInteger('ifInNUcastPkts_delta')->nullable()->change();
            $table->bigInteger('ifOutNUcastPkts')->nullable()->change();
            $table->bigInteger('ifOutNUcastPkts_prev')->nullable()->change();
            $table->bigInteger('ifOutNUcastPkts_delta')->nullable()->change();
            $table->bigInteger('ifInDiscards')->nullable()->change();
            $table->bigInteger('ifInDiscards_prev')->nullable()->change();
            $table->bigInteger('ifInDiscards_delta')->nullable()->change();
            $table->bigInteger('ifOutDiscards')->nullable()->change();
            $table->bigInteger('ifOutDiscards_prev')->nullable()->change();
            $table->bigInteger('ifOutDiscards_delta')->nullable()->change();
            $table->bigInteger('ifInUnknownProtos')->nullable()->change();
            $table->bigInteger('ifInUnknownProtos_prev')->nullable()->change();
            $table->bigInteger('ifInUnknownProtos_delta')->nullable()->change();
            $table->bigInteger('ifInBroadcastPkts')->nullable()->change();
            $table->bigInteger('ifInBroadcastPkts_prev')->nullable()->change();
            $table->bigInteger('ifInBroadcastPkts_delta')->nullable()->change();
            $table->bigInteger('ifOutBroadcastPkts')->nullable()->change();
            $table->bigInteger('ifOutBroadcastPkts_prev')->nullable()->change();
            $table->bigInteger('ifOutBroadcastPkts_delta')->nullable()->change();
            $table->bigInteger('ifInMulticastPkts')->nullable()->change();
            $table->bigInteger('ifInMulticastPkts_prev')->nullable()->change();
            $table->bigInteger('ifInMulticastPkts_delta')->nullable()->change();
            $table->bigInteger('ifOutMulticastPkts')->nullable()->change();
            $table->bigInteger('ifOutMulticastPkts_prev')->nullable()->change();
            $table->bigInteger('ifOutMulticastPkts_delta')->nullable()->change();
        });
    }
};
