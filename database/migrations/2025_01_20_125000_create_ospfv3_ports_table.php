<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ospfv3_ports');
    }
};
