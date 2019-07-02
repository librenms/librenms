<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMplsSdpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpls_sdps', function (Blueprint $table) {
            $table->increments('sdp_id');
            $table->unsignedInteger('sdp_oid');
            $table->unsignedInteger('device_id')->index('device_id');
            $table->enum('sdpRowStatus', array('active','notInService','notReady','createAndGo','createAndWait','destroy'))->nullable();
            $table->enum('sdpDelivery', array('gre','mpls','l2tpv3','greethbridged'))->nullable();
            $table->string('sdpDescription', 80)->nullable();
            $table->enum('sdpAdminStatus', array('up','down'))->nullable();
            $table->enum('sdpOperStatus', array('up','notAlive','notReady','invalidEgressInterface','transportTunnelDown','down'))->nullable();
            $table->integer('sdpAdminPathMtu')->nullable();
            $table->integer('sdpOperPathMtu')->nullable();
            $table->bigInteger('sdpLastMgmtChange')->nullable();
            $table->bigInteger('sdpLastStatusChange')->nullable();
            $table->enum('sdpActiveLspType', array('not-applicable','rsvp','ldp','bgp','none','mplsTp','srIsis','srOspf','srTeLsp','fpe'))->nullable();
            $table->enum('sdpFarEndInetAddressType', array('ipv4','ipv6'))->nullable();
            $table->string('sdpFarEndInetAddress', 46)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpls_sdps');
    }
}
