<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHrSystemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hrSystem', function (Blueprint $table) {
            $table->increments('hrSystem_id');
            $table->unsignedInteger('device_id')->index();
            $table->integer('hrSystemNumUsers')->default(0);
            $table->integer('hrSystemProcesses')->default(0);
            $table->integer('hrSystemMaxProcesses')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hrSystem');
    }
}
