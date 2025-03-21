<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('ospfv3_ports');
        Schema::create('ospfv3_ports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('ospfv3_instance_id');
            $table->unsignedInteger('port_id')->nullable();
            $table->unsignedInteger('ospfv3IfIndex');
            $table->unsignedInteger('ospfv3IfInstId');
            $table->unsignedInteger('ospfv3IfAreaId');
            $table->string('ospfv3IfType', 32);
            $table->string('ospfv3IfAdminStatus', 32);
            $table->unsignedInteger('ospfv3IfRtrPriority');
            $table->unsignedInteger('ospfv3IfTransitDelay');
            $table->unsignedInteger('ospfv3IfRetransInterval');
            $table->unsignedInteger('ospfv3IfHelloInterval');
            $table->unsignedInteger('ospfv3IfRtrDeadInterval');
            $table->unsignedInteger('ospfv3IfPollInterval');
            $table->string('ospfv3IfState', 32);
            $table->string('ospfv3IfDesignatedRouter', 32);
            $table->string('ospfv3IfBackupDesignatedRouter', 32);
            $table->unsignedInteger('ospfv3IfEvents');
            $table->string('ospfv3IfDemand', 32);
            $table->unsignedInteger('ospfv3IfMetricValue');
            $table->unsignedInteger('ospfv3IfLinkScopeLsaCount')->nullable();
            $table->unsignedInteger('ospfv3IfLinkLsaCksumSum')->nullable();
            $table->string('ospfv3IfDemandNbrProbe', 32)->nullable();
            $table->unsignedInteger('ospfv3IfDemandNbrProbeRetransLimit')->nullable();
            $table->unsignedInteger('ospfv3IfDemandNbrProbeInterval')->nullable();
            $table->string('ospfv3IfTEDisabled', 32)->nullable();
            $table->string('ospfv3IfLinkLSASuppression', 32)->nullable();
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'ospfv3IfIndex', 'ospfv3IfInstId', 'context_name'], 'ospfv3_ports_device_id_index_context_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ospfv3_ports');
        Schema::create('ospfv3_ports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id');
            $table->unsignedInteger('ospfv3_instance_id');
            $table->string('ospfv3_port_id', 32);
            $table->integer('ospfv3IfIndex');
            $table->integer('ospfv3IfInstId');
            $table->string('ospfv3IfAreaId', 32);
            $table->string('ospfv3IfType', 32)->nullable();
            $table->string('ospfv3IfAdminStatus', 32)->nullable();
            $table->integer('ospfv3IfRtrPriority')->nullable();
            $table->integer('ospfv3IfTransitDelay')->nullable();
            $table->integer('ospfv3IfRetransInterval')->nullable();
            $table->integer('ospfv3IfHelloInterval')->nullable();
            $table->integer('ospfv3IfRtrDeadInterval')->nullable();
            $table->integer('ospfv3IfPollInterval')->nullable();
            $table->string('ospfv3IfState', 32)->nullable();
            $table->string('ospfv3IfDesignatedRouter', 32)->nullable();
            $table->string('ospfv3IfBackupDesignatedRouter', 32)->nullable();
            $table->integer('ospfv3IfEvents')->nullable();
            $table->string('ospfv3IfDemand', 32)->nullable();
            $table->integer('ospfv3IfMetricValue')->nullable();
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'ospfv3_instance_id', 'ospfv3_port_id', 'context_name'], 'ospfv3_ports_device_id_ospfv3_port_id_context_name_unique');
        });
    }
};
