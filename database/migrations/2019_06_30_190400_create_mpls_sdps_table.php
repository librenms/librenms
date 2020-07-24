<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('device_id')->index();
            $table->enum('sdpRowStatus', ['active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy'])->nullable();
            $table->enum('sdpDelivery', ['gre', 'mpls', 'l2tpv3', 'greethbridged'])->nullable();
            $table->string('sdpDescription', 80)->nullable();
            $table->enum('sdpAdminStatus', ['up', 'down'])->nullable();
            $table->enum('sdpOperStatus', ['up', 'notAlive', 'notReady', 'invalidEgressInterface', 'transportTunnelDown', 'down'])->nullable();
            $table->integer('sdpAdminPathMtu')->nullable();
            $table->integer('sdpOperPathMtu')->nullable();
            $table->bigInteger('sdpLastMgmtChange')->nullable();
            $table->bigInteger('sdpLastStatusChange')->nullable();
            $table->enum('sdpActiveLspType', ['not-applicable', 'rsvp', 'ldp', 'bgp', 'none', 'mplsTp', 'srIsis', 'srOspf', 'srTeLsp', 'fpe'])->nullable();
            $table->enum('sdpFarEndInetAddressType', ['ipv4', 'ipv6'])->nullable();
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
