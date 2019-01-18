<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBgpPeersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bgpPeers', function (Blueprint $table) {
            $table->increments('bgpPeer_id');
            $table->unsignedInteger('device_id');
            $table->string('astext');
            $table->text('bgpPeerIdentifier', 65535);
            $table->bigInteger('bgpPeerRemoteAs');
            $table->text('bgpPeerState', 65535);
            $table->text('bgpPeerAdminStatus', 65535);
            $table->text('bgpLocalAddr', 65535);
            $table->text('bgpPeerRemoteAddr', 65535);
            $table->string('bgpPeerDescr')->default('');
            $table->integer('bgpPeerInUpdates');
            $table->integer('bgpPeerOutUpdates');
            $table->integer('bgpPeerInTotalMessages');
            $table->integer('bgpPeerOutTotalMessages');
            $table->integer('bgpPeerFsmEstablishedTime');
            $table->integer('bgpPeerInUpdateElapsedTime');
            $table->string('context_name', 128)->nullable();
            $table->index(['device_id','context_name'], 'device_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bgpPeers');
    }
}
