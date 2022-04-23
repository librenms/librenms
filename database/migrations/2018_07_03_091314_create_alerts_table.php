<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id')->index();
            $table->unsignedInteger('rule_id')->index();
            $table->integer('state');
            $table->integer('alerted');
            $table->integer('open');
            $table->text('note')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->text('info');
            $table->unique(['device_id', 'rule_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alerts');
    }
}
