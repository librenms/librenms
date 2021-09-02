<?php

/*
    This migration adds primary key for table ipv4_mac.

    Percona Xtradb refuses to modify a table
    without a primary key.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPrimaryKeyIpv4Mac extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('ipv4_mac', 'id')) {
            Schema::table('ipv4_mac', function (Blueprint $table) {
                $table->id()->first();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
