<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->increments('port_id');
            $table->unsignedInteger('device_id')->default(0);
            $table->string('port_descr_type')->nullable();
            $table->string('port_descr_descr')->nullable();
            $table->string('port_descr_circuit')->nullable();
            $table->string('port_descr_speed', 32)->nullable();
            $table->string('port_descr_notes')->nullable();
            $table->string('ifDescr')->nullable()->index();
            $table->string('ifName')->nullable();
            $table->string('portName', 128)->nullable();
            $table->bigInteger('ifIndex')->nullable()->default(0);
            $table->bigInteger('ifSpeed')->nullable();
            $table->string('ifConnectorPresent', 12)->nullable();
            $table->string('ifPromiscuousMode', 12)->nullable();
            $table->integer('ifHighSpeed')->nullable();
            $table->string('ifOperStatus', 16)->nullable();
            $table->string('ifOperStatus_prev', 16)->nullable();
            $table->string('ifAdminStatus', 16)->nullable();
            $table->string('ifAdminStatus_prev', 16)->nullable();
            $table->string('ifDuplex', 12)->nullable();
            $table->integer('ifMtu')->nullable();
            $table->text('ifType')->nullable();
            $table->text('ifAlias')->nullable();
            $table->text('ifPhysAddress')->nullable();
            $table->string('ifHardType', 64)->nullable();
            $table->bigInteger('ifLastChange')->unsigned()->default(0);
            $table->string('ifVlan', 8)->default('');
            $table->string('ifTrunk', 16)->nullable();
            $table->integer('ifVrf')->default(0);
            $table->integer('counter_in')->nullable();
            $table->integer('counter_out')->nullable();
            $table->boolean('ignore')->default(0);
            $table->boolean('disabled')->default(0);
            $table->boolean('detailed')->default(0);
            $table->boolean('deleted')->default(0);
            $table->string('pagpOperationMode', 32)->nullable();
            $table->string('pagpPortState', 16)->nullable();
            $table->string('pagpPartnerDeviceId', 48)->nullable();
            $table->string('pagpPartnerLearnMethod', 16)->nullable();
            $table->integer('pagpPartnerIfIndex')->nullable();
            $table->integer('pagpPartnerGroupIfIndex')->nullable();
            $table->string('pagpPartnerDeviceName', 128)->nullable();
            $table->string('pagpEthcOperationMode', 16)->nullable();
            $table->string('pagpDeviceId', 48)->nullable();
            $table->integer('pagpGroupIfIndex')->nullable();
            $table->unsignedBigInteger('ifInUcastPkts')->nullable();
            $table->unsignedBigInteger('ifInUcastPkts_prev')->nullable();
            $table->unsignedBigInteger('ifInUcastPkts_delta')->nullable();
            $table->unsignedBigInteger('ifInUcastPkts_rate')->nullable();
            $table->unsignedBigInteger('ifOutUcastPkts')->nullable();
            $table->unsignedBigInteger('ifOutUcastPkts_prev')->nullable();
            $table->unsignedBigInteger('ifOutUcastPkts_delta')->nullable();
            $table->unsignedBigInteger('ifOutUcastPkts_rate')->nullable();
            $table->unsignedBigInteger('ifInErrors')->nullable();
            $table->unsignedBigInteger('ifInErrors_prev')->nullable();
            $table->unsignedBigInteger('ifInErrors_delta')->nullable();
            $table->unsignedBigInteger('ifInErrors_rate')->nullable();
            $table->unsignedBigInteger('ifOutErrors')->nullable();
            $table->unsignedBigInteger('ifOutErrors_prev')->nullable();
            $table->unsignedBigInteger('ifOutErrors_delta')->nullable();
            $table->unsignedBigInteger('ifOutErrors_rate')->nullable();
            $table->unsignedBigInteger('ifInOctets')->nullable();
            $table->unsignedBigInteger('ifInOctets_prev')->nullable();
            $table->unsignedBigInteger('ifInOctets_delta')->nullable();
            $table->unsignedBigInteger('ifInOctets_rate')->nullable();
            $table->unsignedBigInteger('ifOutOctets')->nullable();
            $table->unsignedBigInteger('ifOutOctets_prev')->nullable();
            $table->unsignedBigInteger('ifOutOctets_delta')->nullable();
            $table->unsignedBigInteger('ifOutOctets_rate')->nullable();
            $table->unsignedInteger('poll_time')->nullable();
            $table->unsignedInteger('poll_prev')->nullable();
            $table->unsignedInteger('poll_period')->nullable();
            $table->unique(['device_id', 'ifIndex']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ports');
    }
}
