<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('device_id')->index();
            $table->string('sapEncapValue', 255)->nullable();
            $table->enum('sapRowStatus', ['active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy'])->nullable();
            $table->enum('sapType', ['unknown', 'epipe', 'tls', 'vprn', 'ies', 'mirror', 'apipe', 'fpipe', 'ipipe', 'cpipe', 'intTls', 'evpnIsaTls'])->nullable();
            $table->string('sapDescription', 80)->nullable();
            $table->enum('sapAdminStatus', ['up', 'down'])->nullable();
            $table->enum('sapOperStatus', ['up', 'down'])->nullable();
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
