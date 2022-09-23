<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('device_id')->index();
            $table->enum('svcRowStatus', ['active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy'])->nullable();
            $table->enum('svcType', ['unknown', 'epipe', 'tls', 'vprn', 'ies', 'mirror', 'apipe', 'fpipe', 'ipipe', 'cpipe', 'intTls', 'evpnIsaTls'])->nullable();
            $table->unsignedInteger('svcCustId')->nullable();
            $table->enum('svcAdminStatus', ['up', 'down'])->nullable();
            $table->enum('svcOperStatus', ['up', 'down'])->nullable();
            $table->string('svcDescription', 80)->nullable();
            $table->integer('svcMtu')->nullable();
            $table->integer('svcNumSaps')->nullable();
            $table->integer('svcNumSdps')->nullable();
            $table->bigInteger('svcLastMgmtChange')->nullable();
            $table->bigInteger('svcLastStatusChange')->nullable();
            $table->integer('svcVRouterId')->nullable();
            $table->enum('svcTlsMacLearning', ['enabled', 'disabled'])->nullable();
            $table->enum('svcTlsStpAdminStatus', ['enabled', 'disabled'])->nullable();
            $table->enum('svcTlsStpOperStatus', ['up', 'down'])->nullable();
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
