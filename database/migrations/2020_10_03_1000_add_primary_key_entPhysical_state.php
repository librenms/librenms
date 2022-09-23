<?php

/*
    This migration adds primary key for table entPhysical_state.

    Percona Xtradb refuses to modify a table
    without a primary key.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPrimaryKeyEntphysicalState extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('entPhysical_state', 'id')) {
            Schema::table('entPhysical_state', function (Blueprint $table) {
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
