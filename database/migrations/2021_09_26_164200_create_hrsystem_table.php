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
            $table->string('key', 32);
            $table->string('value', 32);
            $table->string('value_prev', 32);
            $table->unique(['device_id', 'key']);
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
