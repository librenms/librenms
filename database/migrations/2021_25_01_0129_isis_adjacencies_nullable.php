<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IsisAdjacenciesNullable extends Migration
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
            $table->string('isisISAdjNeighSysType', 128)->nullable()->change();
            $table->string('isisISAdjNeighSysID', 128)->nullable()->change();
            $table->string('isisISAdjNeighPriority', 128)->nullable()->change();
            $table->unsignedBigInteger('isisISAdjLastUpTime')->nullable()->change();
            $table->string('isisISAdjAreaAddress', 128)->nullable()->change();
            $table->string('isisISAdjIPAddrType', 128)->nullable()->change();
            $table->string('isisISAdjIPAddrAddress', 128)->nullable()->change();
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
            $table->string('isisISAdjNeighSysType', 128)->change();
            $table->string('isisISAdjNeighSysID', 128)->change();
            $table->string('isisISAdjNeighPriority', 128)->change();
            $table->unsignedBigInteger('isisISAdjLastUpTime')->change();
            $table->string('isisISAdjAreaAddress', 128)->change();
            $table->string('isisISAdjIPAddrType', 128)->change();
            $table->string('isisISAdjIPAddrAddress', 128)->change();
        });
    }
}
