<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIpv4NetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ipv4_networks', function (Blueprint $table) {
            $table->increments('ipv4_network_id');
            $table->string('ipv4_network', 64);
            $table->string('context_name', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ipv4_networks');
    }
}
