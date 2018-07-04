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
            $table->integer('port_vlan_id', true);
            $table->integer('device_id');
            $table->integer('port_id');
            $table->integer('vlan');
            $table->integer('baseport');
            $table->bigInteger('priority');
            $table->string('state', 16);
            $table->integer('cost');
            $table->boolean('untagged')->default(0);
            $table->unique(['device_id','port_id','vlan'], 'unique');
        });

        \DB::statement("ALTER TABLE `ports_vlans` CHANGE `priority` `priority` bigint(32) NOT NULL ;");
        \DB::statement("ALTER TABLE `ports_vlans` CHANGE `untagged` `untagged` tinyint(4) NOT NULL DEFAULT '0' ;");
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
