<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOspfTosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ospf_tos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->string('ospf_port_id', 32);
            $table->string('ospfIfMetricIpAddress', 32);
            $table->integer('ospfIfMetricAddressLessIf');
            $table->integer('ospfIfMetricTOS');
            $table->integer('ospfIfMetricValue');
            $table->integer('ospfIfMetricStatus');
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
        Schema::dropIfExists('ospf_tos');
    }
}
