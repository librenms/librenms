<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('bgpPeers', function (Blueprint $table) {
            $table->increments('bgpPeer_id');
            $table->unsignedInteger('device_id');
            $table->string('astext');
            $table->text('bgpPeerIdentifier');
            $table->bigInteger('bgpPeerRemoteAs');
            $table->text('bgpPeerState');
            $table->text('bgpPeerAdminStatus');
            $table->text('bgpLocalAddr');
            $table->text('bgpPeerRemoteAddr');
            $table->string('bgpPeerDescr')->default('');
            $table->integer('bgpPeerInUpdates');
            $table->integer('bgpPeerOutUpdates');
            $table->integer('bgpPeerInTotalMessages');
            $table->integer('bgpPeerOutTotalMessages');
            $table->integer('bgpPeerFsmEstablishedTime');
            $table->integer('bgpPeerInUpdateElapsedTime');
            $table->string('context_name', 128)->nullable();
            $table->index(['device_id', 'context_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('bgpPeers');
    }
};
