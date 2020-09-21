<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTonerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toner', function (Blueprint $table) {
            $table->increments('toner_id');
            $table->unsignedInteger('device_id')->default(0)->index();
            $table->integer('toner_index');
            $table->string('toner_type', 64);
            $table->string('toner_oid', 64);
            $table->string('toner_descr', 32)->default('');
            $table->integer('toner_capacity')->default(0);
            $table->integer('toner_current')->default(0);
            $table->string('toner_capacity_oid', 64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('toner');
    }
}
