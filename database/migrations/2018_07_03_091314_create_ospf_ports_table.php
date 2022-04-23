<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOspfPortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ospf_ports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id');
            $table->string('ospf_port_id', 32);
            $table->string('ospfIfIpAddress', 32);
            $table->integer('ospfAddressLessIf');
            $table->string('ospfIfAreaId', 32);
            $table->string('ospfIfType', 32)->nullable();
            $table->string('ospfIfAdminStat', 32)->nullable();
            $table->integer('ospfIfRtrPriority')->nullable();
            $table->integer('ospfIfTransitDelay')->nullable();
            $table->integer('ospfIfRetransInterval')->nullable();
            $table->integer('ospfIfHelloInterval')->nullable();
            $table->integer('ospfIfRtrDeadInterval')->nullable();
            $table->integer('ospfIfPollInterval')->nullable();
            $table->string('ospfIfState', 32)->nullable();
            $table->string('ospfIfDesignatedRouter', 32)->nullable();
            $table->string('ospfIfBackupDesignatedRouter', 32)->nullable();
            $table->integer('ospfIfEvents')->nullable();
            $table->string('ospfIfAuthKey', 128)->nullable();
            $table->string('ospfIfStatus', 32)->nullable();
            $table->string('ospfIfMulticastForwarding', 32)->nullable();
            $table->string('ospfIfDemand', 32)->nullable();
            $table->string('ospfIfAuthType', 32)->nullable();
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'ospf_port_id', 'context_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ospf_ports');
    }
}
