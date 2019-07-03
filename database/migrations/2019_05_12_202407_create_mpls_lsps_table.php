<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMplsLspsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpls_lsps', function (Blueprint $table) {
            $table->increments('lsp_id');
            $table->unsignedInteger('vrf_oid');
            $table->unsignedInteger('lsp_oid');
            $table->unsignedInteger('device_id')->index('device_id');
            $table->enum('mplsLspRowStatus', array('active','notInService','notReady','createAndGo','createAndWait','destroy'));
            $table->bigInteger('mplsLspLastChange')->nullable();
            $table->string('mplsLspName', 64);
            $table->enum('mplsLspAdminState', array('noop','inService','outOfService'));
            $table->enum('mplsLspOperState', array('unknown','inService','outOfService','transition'));
            $table->string('mplsLspFromAddr', 32);
            $table->string('mplsLspToAddr', 32);
            $table->enum('mplsLspType', array('unknown','dynamic','static','bypassOnly','p2mpLsp','p2mpAuto','mplsTp','meshP2p','oneHopP2p','srTe','meshP2pSrTe','oneHopP2pSrTe'));
            $table->enum('mplsLspFastReroute', array('true','false'));
            $table->bigInteger('mplsLspAge')->nullable();
            $table->bigInteger('mplsLspTimeUp')->nullable();
            $table->bigInteger('mplsLspTimeDown')->nullable();
            $table->bigInteger('mplsLspPrimaryTimeUp')->nullable();
            $table->unsignedInteger('mplsLspTransitions')->nullable();
            $table->bigInteger('mplsLspLastTransition')->nullable();
            $table->unsignedInteger('mplsLspConfiguredPaths')->nullable();
            $table->unsignedInteger('mplsLspStandbyPaths')->nullable();
            $table->unsignedInteger('mplsLspOperationalPaths')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpls_lsps');
    }
}
