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
            $table->bigInteger('ifInNUcastPkts_delta')->nullable()->change();
            $table->bigInteger('ifOutNUcastPkts_delta')->nullable()->change();
            $table->bigInteger('ifInDiscards_delta')->nullable()->change();
            $table->bigInteger('ifOutDiscards_delta')->nullable()->change();
            $table->bigInteger('ifInUnknownProtos_delta')->nullable()->change();
            $table->bigInteger('ifInBroadcastPkts_delta')->nullable()->change();
            $table->bigInteger('ifOutBroadcastPkts_delta')->nullable()->change();
            $table->bigInteger('ifInMulticastPkts_delta')->nullable()->change();
            $table->bigInteger('ifOutMulticastPkts_delta')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
