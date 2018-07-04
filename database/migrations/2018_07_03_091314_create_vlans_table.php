<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVlansTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vlans', function (Blueprint $table) {
            $table->integer('vlan_id', true);
            $table->integer('device_id')->nullable();
            $table->integer('vlan_vlan')->nullable();
            $table->integer('vlan_domain')->nullable();
            $table->string('vlan_name', 64)->nullable();
            $table->string('vlan_type', 16)->nullable();
            $table->integer('vlan_mtu')->nullable();
            $table->index(['device_id','vlan_vlan'], 'device_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vlans');
    }
}
