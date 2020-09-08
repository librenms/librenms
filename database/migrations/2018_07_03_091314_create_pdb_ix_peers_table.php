<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePdbIxPeersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdb_ix_peers', function (Blueprint $table) {
            $table->increments('pdb_ix_peers_id');
            $table->unsignedInteger('ix_id');
            $table->unsignedInteger('peer_id');
            $table->unsignedInteger('remote_asn');
            $table->string('remote_ipaddr4', 15)->nullable();
            $table->string('remote_ipaddr6', 128)->nullable();
            $table->string('name')->nullable();
            $table->integer('timestamp')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pdb_ix_peers');
    }
}
