<?php

/*
    This migration adds primary key for device_graphs

    Percona Xtradb refused to INSERT IGNORE into a table
    without a primary key.
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPrimaryKeyToDeviceGraphs extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('device_graphs')->delete();

        Schema::table('device_graphs', function (Blueprint $table) {
            $table->primary(['device_id', 'graph']);
            $table->dropIndex('device_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_graphs', function (Blueprint $table) {
            $table->dropPrimary(['device_id', 'graph']);
        });

        Schema::table('device_graphs', function (Blueprint $table) {
            $table->string('graph')->nullable()->change();
            $table->unsignedInteger('device_id')->index('device_id')->change();
        });
    }
}
