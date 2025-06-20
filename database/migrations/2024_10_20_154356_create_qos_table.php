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
        Schema::create('qos', function (Blueprint $table) {
            $table->id('qos_id');
            $table->timestamps();
            $table->integer('device_id')->unsigned()->index();
            $table->integer('port_id')->unsigned()->nullable()->index();
            $table->bigInteger('parent_id')->unsigned()->nullable()->index();
            $table->string('type', 50)->index()->comment('Type of QoS');
            $table->string('title')->comment('Graph Title');
            $table->longText('tooltip')->nullable();
            $table->string('snmp_idx')->comment('SNMP Index for polling QoS data');
            $table->string('rrd_id')->comment('Suffix for the RRD file to identify this QoS');
            $table->boolean('ingress')->default(0)->comment('Does this process ingress bytes');
            $table->boolean('egress')->default(0)->comment('Does this process egress bytes');
            $table->boolean('disabled')->default(0)->comment('Should this QoS be polled');
            $table->boolean('ignore')->default(0)->comment('Should this QoS be alerted on');
            $table->bigInteger('last_polled')->nullable()->comment('Last polled time for calculating rate');
            $table->bigInteger('max_in')->nullable()->comment('Maximum value for input data if defined');
            $table->bigInteger('max_out')->nullable()->comment('Maximum value for output data if defined');
            $table->bigInteger('last_bytes_in')->nullable()->comment('Last polled counter for input bytes');
            $table->bigInteger('last_bytes_out')->nullable()->comment('Last polled counter for output bytes');
            $table->bigInteger('bytes_in_rate')->nullable()->comment('Output rate for bytes');
            $table->bigInteger('bytes_out_rate')->nullable()->comment('Input rate for bytes');
            $table->bigInteger('last_bytes_drop_in')->nullable()->comment('Last polled counter for input dropped bytes');
            $table->bigInteger('last_bytes_drop_out')->nullable()->comment('Last polled counter for output dropped bytes');
            $table->bigInteger('bytes_drop_in_rate')->nullable()->comment('Output rate for dropped bytes');
            $table->bigInteger('bytes_drop_out_rate')->nullable()->comment('Input rate for dropped bytes');
            $table->bigInteger('last_packets_in')->nullable()->comment('Last polled counter for input packets');
            $table->bigInteger('last_packets_out')->nullable()->comment('Last polled counter for output packets');
            $table->bigInteger('packets_in_rate')->nullable()->comment('Output rate for packets');
            $table->bigInteger('packets_out_rate')->nullable()->comment('Input rate for packets');
            $table->bigInteger('last_packets_drop_in')->nullable()->comment('Last polled counter for input dropped packets');
            $table->bigInteger('last_packets_drop_out')->nullable()->comment('Last polled counter for output dropped packets');
            $table->bigInteger('packets_drop_in_rate')->nullable()->comment('Output rate for dropped packets');
            $table->bigInteger('packets_drop_out_rate')->nullable()->comment('Input rate for dropped packets');
            $table->decimal('bytes_drop_in_pct', 6, 2)->nullable()->comment('Percentage of input bytes dropped');
            $table->decimal('bytes_drop_out_pct', 6, 2)->nullable()->comment('Percentage of output bytes dropped');
            $table->decimal('packets_drop_in_pct', 6, 2)->nullable()->comment('Percentage of input packets dropped');
            $table->decimal('packets_drop_out_pct', 6, 2)->nullable()->comment('Percentage of output packets dropped');
            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('CASCADE');
            $table->foreign('port_id')->references('port_id')->on('ports')->onDelete('set null');
            $table->foreign('parent_id')->references('qos_id')->on('qos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qos');
    }
};
