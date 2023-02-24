<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PortsStpDesignatedCostChangeToInt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->unsignedInteger('designatedCost')->change();
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
            $table->smallInteger('designatedCost')->unsigned()->change();
        });
    }
}
