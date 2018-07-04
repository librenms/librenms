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
            $table->integer('id', true);
            $table->integer('device_id')->index('device_id');
            $table->integer('rule_id')->index('rule_id');
            $table->integer('state');
            $table->integer('alerted');
            $table->integer('open');
            $table->text('note', 65535)->nullable();
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unique(['device_id','rule_id'], 'unique_alert');
        });

        \DB::statement("ALTER TABLE `alerts` CHANGE `timestamp` `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;");
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
