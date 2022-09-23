<?php

/*
    This migration adds primary key for table juniAtmVp.

    Percona Xtradb refuses to modify a table
    without a primary key.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPrimaryKeyJuniatmvp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('juniAtmVp', 'id')) {
            Schema::table('juniAtmVp', function (Blueprint $table) {
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
