<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBasePortToPortsStpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->unsignedInteger('dot1dBasePort')->after('port_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->dropColumn('dot1dBasePort');
        });
    }
}
