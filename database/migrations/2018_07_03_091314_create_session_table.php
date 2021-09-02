<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session', function (Blueprint $table) {
            $table->increments('session_id');
            $table->string('session_username');
            $table->string('session_value', 60)->unique();
            $table->string('session_token', 60);
            $table->string('session_auth', 16);
            $table->integer('session_expiry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('session');
    }
}
