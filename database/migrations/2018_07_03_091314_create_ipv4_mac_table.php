<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIpv4MacTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ipv4_mac', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('port_id')->index();
            $table->unsignedInteger('device_id')->nullable();
            $table->string('mac_address', 32)->index();
            $table->string('ipv4_address', 32);
            $table->string('context_name', 128);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ipv4_mac');
    }
}
