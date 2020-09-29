<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePseudowiresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pseudowires', function (Blueprint $table) {
            $table->increments('pseudowire_id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id');
            $table->unsignedInteger('peer_device_id');
            $table->integer('peer_ldp_id');
            $table->integer('cpwVcID');
            $table->integer('cpwOid');
            $table->string('pw_type', 32);
            $table->string('pw_psntype', 32);
            $table->integer('pw_local_mtu');
            $table->integer('pw_peer_mtu');
            $table->string('pw_descr', 128);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pseudowires');
    }
}
