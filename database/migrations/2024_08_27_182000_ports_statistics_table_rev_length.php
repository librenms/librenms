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
            $table->bigInteger('ifInNUcastPkts_rate')->nullable()->change();
            $table->bigInteger('ifOutNUcastPkts_rate')->nullable()->change();
            $table->bigInteger('ifInDiscards_rate')->nullable()->change();
            $table->bigInteger('ifOutDiscards_rate')->nullable()->change();
            $table->bigInteger('ifInUnknownProtos_rate')->nullable()->change();
            $table->bigInteger('ifInBroadcastPkts_rate')->nullable()->change();
            $table->bigInteger('ifOutBroadcastPkts_rate')->nullable()->change();
            $table->bigInteger('ifInMulticastPkts_rate')->nullable()->change();
            $table->bigInteger('ifOutMulticastPkts_rate')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ports_statistics', function (Blueprint $table) {
            $table->integer('ifInNUcastPkts_rate')->nullable()->change();
            $table->integer('ifOutNUcastPkts_rate')->nullable()->change();
            $table->integer('ifInDiscards_rate')->nullable()->change();
            $table->integer('ifOutDiscards_rate')->nullable()->change();
            $table->integer('ifInUnknownProtos_rate')->nullable()->change();
            $table->integer('ifInBroadcastPkts_rate')->nullable()->change();
            $table->integer('ifOutBroadcastPkts_rate')->nullable()->change();
            $table->integer('ifInMulticastPkts_rate')->nullable()->change();
            $table->integer('ifOutMulticastPkts_rate')->nullable()->change();
        });
    }
};
