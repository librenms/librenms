<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IsisAdjacenciesPortNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('isis_adjacencies', function (Blueprint $table) {
            $table->integer('port_id')->nullable()->change();
            $table->integer('isisISAdjNeighSysType')->nullable()->change();
            $table->integer('isisISAdjNeighSysID')->nullable()->change();
            $table->integer('isisISAdjNeighPriority')->nullable()->change();
            $table->integer('isisISAdjLastUpTime')->nullable()->change();
            $table->integer('isisISAdjAreaAddress')->nullable()->change();
            $table->integer('isisISAdjIPAddrType')->nullable()->change();
            $table->integer('isisISAdjIPAddrAddress')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('isis_adjacencies', function (Blueprint $table) {
            $table->integer('port_id')->change();
            $table->integer('isisISAdjNeighSysType')->change();
            $table->integer('isisISAdjNeighSysID')->change();
            $table->integer('isisISAdjNeighPriority')->change();
            $table->integer('isisISAdjLastUpTime')->change();
            $table->integer('isisISAdjAreaAddress')->change();
            $table->integer('isisISAdjIPAddrType')->change();
            $table->integer('isisISAdjIPAddrAddress')->change();
        });
    }
}
