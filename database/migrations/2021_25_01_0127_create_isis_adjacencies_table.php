<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIsisAdjacenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('isis_adjacencies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->index();
            $table->integer('port_id')->index();
            $table->integer('ifIndex')->index();
            $table->string('isisISAdjState', 13);
            $table->string('isisISAdjNeighSysType', 128);
            $table->string('isisISAdjNeighSysID', 128);
            $table->string('isisISAdjNeighPriority', 128);
            $table->unsignedBigInteger('isisISAdjLastUpTime');
            $table->string('isisISAdjAreaAddress', 128);
            $table->string('isisISAdjIPAddrType', 128);
            $table->string('isisISAdjIPAddrAddress', 128);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('isis_adjacencies');
    }
}
