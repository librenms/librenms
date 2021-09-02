<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePollersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pollers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('poller_name')->unique();
            $table->dateTime('last_polled');
            $table->unsignedInteger('devices');
            $table->float('time_taken', 10, 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pollers');
    }
}
