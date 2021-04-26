<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortsVlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ports_vlans', function (Blueprint $table) {
            $table->increments('port_vlan_id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id');
            $table->integer('vlan');
            $table->integer('baseport');
            $table->bigInteger('priority');
            $table->string('state', 16);
            $table->integer('cost');
            $table->boolean('untagged')->default(0);
            $table->unique(['device_id', 'port_id', 'vlan']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ports_vlans');
    }
}
