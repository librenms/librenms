<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventlog', function (Blueprint $table) {
            $table->increments('event_id');
            $table->unsignedInteger('device_id')->nullable()->index();
            $table->dateTime('datetime')->default('1970-01-02 00:00:01')->index();
            $table->text('message')->nullable();
            $table->string('type', 64)->nullable();
            $table->string('reference', 64)->nullable();
            $table->string('username', 128)->nullable();
            $table->tinyInteger('severity')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('eventlog');
    }
}
