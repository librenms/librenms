<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authlog', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('datetime')->useCurrent();
            $table->text('user');
            $table->text('address');
            $table->text('result');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('authlog');
    }
}
