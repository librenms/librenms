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
        });
    }
}
