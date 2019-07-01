<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSdpVcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sdp-vcs', function (Blueprint $table) {
            $table->increments('vc_id');
            $table->unsignedInteger('vc_oid');
            $table->unsignedInteger('device_id')->index('device_id');
            $table->enum('sdpBindRowStatus', array('active','notInService','notReady','createAndGo','createAndWait','destroy'));
            $table->enum('sdpBindAdminStatus', array('up','down'));
            $table->enum('sdpBindOperStatus', array('up','down'));
            $table->bigInteger('sdpBindLastMgmtChange')->nullable();
            $table->bigInteger('sdpBindLastStatusChange')->nullable();
            $table->enum('sdpBindType', array('spoke','mesh'));
            $table->enum('sdpBindVcType', array('undef','ether','vlan','mirrior','atmSdu'.'atmCell','atmVcc','atmVpc','frDlci','ipipe','satopE1','satopT1','satopE3','satopT3','cesopsn','cesopsnCas'));
            $table->bigInteger('sdpBindBaseStatsIngressForwardedPackets')->nullable();
            $table->bigInteger('sdpBindBaseStatsIngFwdOctets')->nullable();
            $table->bigInteger('sdpBindBaseStatsEgressForwardedPackets')->nullable();
            $table->bigInteger('sdpBindBaseStatsEgressForwardedOctets')->nullable();
            $table->enum('svcRowStatus', array('active','notInService','notReady','createAndGo','createAndWait','destroy'));
            $table->enum('svcType', array('spoke','mesh'));
            $table->unsignedInteger('svcCustId');
            $table->enum('svcAdminStatus', array('up','down'));
            $table->enum('svcdOperStatus', array('up','down'));
            $table->string('svcDescription', 80)->nullable();
            $table->integer('svcMtu')->nullable();
            $table->integer('svcNumSaps')->nullable();
            $table->bigInteger('svcLastMgmtChange')->nullable();
            $table->bigInteger('svcLastStatusChange')->nullable();
            $table->integer('svcVRouterId')->nullable();
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
        Schema::dropIfExists('sdp-vcs');
    }
}
