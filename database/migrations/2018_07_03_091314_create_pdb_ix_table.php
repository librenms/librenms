<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePdbIxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdb_ix', function (Blueprint $table) {
            $table->increments('pdb_ix_id');
            $table->unsignedInteger('ix_id')->unsigned();
            $table->string('name');
            $table->integer('asn')->unsigned();
            $table->integer('timestamp')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pdb_ix');
    }
}
