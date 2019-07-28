<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMplsServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpls_services', function (Blueprint $table) {
            $table->increments('svc_id');
            $table->unsignedInteger('svc_oid');
            $table->unsignedInteger('device_id')->index('device_id');
            $table->enum('svcRowStatus', array('active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy'))->nullable();
            $table->enum('svcType', array('unknown', 'epipe', 'tls', 'vprn', 'ies', 'mirror', 'apipe', 'fpipe', 'ipipe', 'cpipe', 'intTls', 'evpnIsaTls'))->nullable();
            $table->unsignedInteger('svcCustId')->nullable();
            $table->enum('svcAdminStatus', array('up', 'down'))->nullable();
            $table->enum('svcOperStatus', array('up', 'down'))->nullable();
            $table->string('svcDescription', 80)->nullable();
            $table->integer('svcMtu')->nullable();
            $table->integer('svcNumSaps')->nullable();
            $table->integer('svcNumSdps')->nullable();
            $table->bigInteger('svcLastMgmtChange')->nullable();
            $table->bigInteger('svcLastStatusChange')->nullable();
            $table->integer('svcVRouterId')->nullable();
            $table->enum('svcTlsMacLearning', array('enabled', 'disabled'))->nullable();
            $table->enum('svcTlsStpAdminStatus', array('enabled', 'disabled'))->nullable();
            $table->enum('svcTlsStpOperStatus', array('up', 'down'))->nullable();
            $table->integer('svcTlsFdbTableSize')->nullable();
            $table->integer('svcTlsFdbNumEntries')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpls_services');
    }
}
