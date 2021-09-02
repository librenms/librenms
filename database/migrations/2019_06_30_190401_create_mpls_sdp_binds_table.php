<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMplsSdpBindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpls_sdp_binds', function (Blueprint $table) {
            $table->increments('bind_id');
            $table->unsignedInteger('sdp_id');
            $table->unsignedInteger('svc_id');
            $table->unsignedInteger('sdp_oid');
            $table->unsignedInteger('svc_oid');
            $table->unsignedInteger('device_id')->index();
            $table->enum('sdpBindRowStatus', ['active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy'])->nullable();
            $table->enum('sdpBindAdminStatus', ['up', 'down'])->nullable();
            $table->enum('sdpBindOperStatus', ['up', 'down'])->nullable();
            $table->bigInteger('sdpBindLastMgmtChange')->nullable();
            $table->bigInteger('sdpBindLastStatusChange')->nullable();
            $table->enum('sdpBindType', ['spoke', 'mesh'])->nullable();
            $table->enum('sdpBindVcType', ['undef', 'ether', 'vlan', 'mirrior', 'atmSdu' . 'atmCell', 'atmVcc', 'atmVpc', 'frDlci', 'ipipe', 'satopE1', 'satopT1', 'satopE3', 'satopT3', 'cesopsn', 'cesopsnCas'])->nullable();
            $table->bigInteger('sdpBindBaseStatsIngFwdPackets')->nullable();
            $table->bigInteger('sdpBindBaseStatsIngFwdOctets')->nullable();
            $table->bigInteger('sdpBindBaseStatsEgrFwdPackets')->nullable();
            $table->bigInteger('sdpBindBaseStatsEgrFwdOctets')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpls_sdp_binds');
    }
}
