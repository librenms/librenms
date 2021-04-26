<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIpsecTunnelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ipsec_tunnels', function (Blueprint $table) {
            $table->increments('tunnel_id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('peer_port');
            $table->string('peer_addr', 64);
            $table->string('local_addr', 64);
            $table->unsignedInteger('local_port');
            $table->string('tunnel_name', 96);
            $table->string('tunnel_status', 11);
            $table->unique(['device_id', 'peer_addr']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ipsec_tunnels');
    }
}
