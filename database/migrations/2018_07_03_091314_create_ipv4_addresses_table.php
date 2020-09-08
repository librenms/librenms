<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIpv4AddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ipv4_addresses', function (Blueprint $table) {
            $table->increments('ipv4_address_id');
            $table->string('ipv4_address', 32);
            $table->integer('ipv4_prefixlen');
            $table->string('ipv4_network_id', 32);
            $table->unsignedInteger('port_id')->index();
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
        Schema::drop('ipv4_addresses');
    }
}
