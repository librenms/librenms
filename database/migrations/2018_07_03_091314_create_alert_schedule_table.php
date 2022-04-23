<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_schedule', function (Blueprint $table) {
            $table->increments('schedule_id');
            $table->boolean('recurring')->default(0)->unsigned();
            $table->dateTime('start')->default('1970-01-02 00:00:01');
            $table->dateTime('end')->default('1970-01-02 00:00:01');
            $table->date('start_recurring_dt')->default('1970-01-01');
            $table->date('end_recurring_dt')->nullable();
            $table->time('start_recurring_hr')->default('00:00:00');
            $table->time('end_recurring_hr')->default('00:00:00');
            $table->string('recurring_day', 15)->nullable();
            $table->string('title');
            $table->text('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_schedule');
    }
}
