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
            $table->timestamp('datetime')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('user', 65535);
            $table->text('address', 65535);
            $table->text('result', 65535);
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
