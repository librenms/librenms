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
        Schema::table('bgpPeers', function (Blueprint $table) {
            $table->integer('bgpPeerInUpdates')->unsigned()->change();
            $table->integer('bgpPeerOutUpdates')->unsigned()->change();
            $table->integer('bgpPeerInTotalMessages')->unsigned()->change();
            $table->integer('bgpPeerOutTotalMessages')->unsigned()->change();
            $table->integer('bgpPeerFsmEstablishedTime')->unsigned()->change();
            $table->integer('bgpPeerInUpdateElapsedTime')->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bgpPeers', function (Blueprint $table) {
            $table->integer('bgpPeerInUpdates')->change();
            $table->integer('bgpPeerOutUpdates')->change();
            $table->integer('bgpPeerInTotalMessages')->change();
            $table->integer('bgpPeerOutTotalMessages')->change();
            $table->integer('bgpPeerFsmEstablishedTime')->change();
            $table->integer('bgpPeerInUpdateElapsedTime')->change();
        });
    }
};
