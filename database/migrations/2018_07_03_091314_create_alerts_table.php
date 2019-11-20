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
            $table->unsignedInteger('device_id')->index('device_id');
            $table->unsignedInteger('rule_id')->index('rule_id');
            $table->integer('state');
            $table->integer('alerted');
            $table->integer('open');
            $table->text('note')->nullable();
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->text('info');
            $table->unique(['device_id','rule_id'], 'unique_alert');
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
