<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
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
    public function down(): void
    {
        Schema::drop('pdb_ix');
    }
};
