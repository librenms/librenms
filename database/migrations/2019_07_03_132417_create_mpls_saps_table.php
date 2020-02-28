<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMplsSapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpls_saps', function (Blueprint $table) {
            $table->increments('sap_id');
            $table->unsignedInteger('svc_id');
            $table->unsignedInteger('svc_oid');
            $table->unsignedInteger('sapPortId');
            $table->string('ifName', 255)->nullable();
            $table->unsignedInteger('device_id')->index('device_id');
            $table->string('sapEncapValue', 255)->nullable();
            $table->enum('sapRowStatus', array('active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy'))->nullable();
            $table->enum('sapType', array('unknown', 'epipe', 'tls', 'vprn', 'ies', 'mirror', 'apipe', 'fpipe', 'ipipe', 'cpipe', 'intTls', 'evpnIsaTls'))->nullable();
            $table->string('sapDescription', 80)->nullable();
            $table->enum('sapAdminStatus', array('up', 'down'))->nullable();
            $table->enum('sapOperStatus', array('up', 'down'))->nullable();
            $table->bigInteger('sapLastMgmtChange')->nullable();
            $table->bigInteger('sapLastStatusChange')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpls_saps');
    }
}
