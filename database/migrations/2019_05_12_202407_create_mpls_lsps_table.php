<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('device_id')->index();
            $table->enum('mplsLspRowStatus', ['active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy']);
            $table->bigInteger('mplsLspLastChange')->nullable();
            $table->string('mplsLspName', 64);
            $table->enum('mplsLspAdminState', ['noop', 'inService', 'outOfService']);
            $table->enum('mplsLspOperState', ['unknown', 'inService', 'outOfService', 'transition']);
            $table->string('mplsLspFromAddr', 32);
            $table->string('mplsLspToAddr', 32);
            $table->enum('mplsLspType', ['unknown', 'dynamic', 'static', 'bypassOnly', 'p2mpLsp', 'p2mpAuto', 'mplsTp', 'meshP2p', 'oneHopP2p', 'srTe', 'meshP2pSrTe', 'oneHopP2pSrTe']);
            $table->enum('mplsLspFastReroute', ['true', 'false']);
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
