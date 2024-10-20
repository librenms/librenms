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
            $table->id();
            $table->timestamps();
            $table->integer('device_id')->unsigned()->index();
            $table->integer('port_id')->unsigned()->nullable()->index();
            $table->bigInteger('parent_id')->unsigned()->nullable()->index();
            $table->string('type', 50)->index()->comment('Type of QoS');
            $table->string('title')->comment('Graph Title');
            $table->string('snmp_idx')->comment('SNMP Index for polling QoS data');
            $table->string('rrd_id')->comment('Suffix for the RRD file to identify this QoS');
            $table->boolean('ingress')->default(0)->comment('Does this process ingress traffic');
            $table->boolean('egress')->default(0)->comment('Does this process egress traffic');
            $table->boolean('disabled')->default(0)->comment('Should this QoS be polled');
            $table->boolean('ignore')->default(0)->comment('Should this QoS be alerted on');
            $table->bigInteger('last_polled')->nullable()->comment('Last polled time for calculating rate');
            $table->bigInteger('max_in')->nullable()->comment('Maximum value for input data if defined');
            $table->bigInteger('max_out')->nullable()->comment('Maximum value for output data if defined');
            $table->bigInteger('last_traffic_in')->nullable()->comment('Last polled counter for input traffic');
            $table->bigInteger('last_traffic_out')->nullable()->comment('Last polled counter for output traffic');
            $table->bigInteger('traffic_in_rate')->nullable()->comment('Output rate for traffic');
            $table->bigInteger('traffic_out_rate')->nullable()->comment('Input rate for traffic');
            $table->bigInteger('last_drop_in')->nullable()->comment('Last polled counter for input dropped traffic');
            $table->bigInteger('last_drop_out')->nullable()->comment('Last polled counter for output dropped traffic');
            $table->bigInteger('drop_in_rate')->nullable()->comment('Output rate for dropped traffic');
            $table->bigInteger('drop_out_rate')->nullable()->comment('Input rate for dropped traffic');
            $table->bigInteger('drop_in_pct')->storedAs('CASE WHEN traffic_in_rate = 0 THEN 0 ELSE 100 * drop_in_rate / traffic_in_rate END')->comment('Percentage of input traffic dropped');
            $table->bigInteger('drop_out_pct')->storedAs('CASE WHEN traffic_out_rate = 0 THEN 0 ELSE 100 * drop_out_rate / traffic_out_rate END')->comment('Percentage of output traffic dropped');
            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('CASCADE');
            $table->foreign('port_id')->references('port_id')->on('ports')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('qos')->onDelete('set null');
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
