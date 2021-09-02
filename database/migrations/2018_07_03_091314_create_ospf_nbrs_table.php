<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOspfNbrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ospf_nbrs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id')->nullable();
            $table->string('ospf_nbr_id', 32);
            $table->string('ospfNbrIpAddr', 32);
            $table->integer('ospfNbrAddressLessIndex');
            $table->string('ospfNbrRtrId', 32);
            $table->integer('ospfNbrOptions');
            $table->integer('ospfNbrPriority');
            $table->string('ospfNbrState', 32);
            $table->integer('ospfNbrEvents');
            $table->integer('ospfNbrLsRetransQLen');
            $table->string('ospfNbmaNbrStatus', 32);
            $table->string('ospfNbmaNbrPermanence', 32);
            $table->string('ospfNbrHelloSuppressed', 32);
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'ospf_nbr_id', 'context_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ospf_nbrs');
    }
}
