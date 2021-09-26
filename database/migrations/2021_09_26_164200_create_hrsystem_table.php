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
            $table->unsignedInteger('device_id')->default(0)->index();
            $table->string('key');
            $table->string('value');
            $table->string('value_prev');
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
