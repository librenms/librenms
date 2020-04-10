<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlertScheduleUtc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alert_schedule', function (Blueprint $table) {
            $table->dateTime('start')->nullable()->default('1970-01-02 00:00:01')->change();
            $table->dateTime('end')->nullable()->default('1970-01-02 00:00:01')->change();
            $table->date('start_recurring_dt')->nullable()->change();
            $table->time('start_recurring_hr')->nullable()->change();
            $table->time('end_recurring_hr')->nullable()->change();
        });

        DB::table('alert_schedule')->update([
            'start' => DB::raw("CONVERT_TZ(start, @@global.time_zone, '+00:00')"),
            'end' => DB::raw("CONVERT_TZ(end, @@global.time_zone, '+00:00')"),
            'start_recurring_dt' => DB::raw("SUBSTRING_INDEX(CONVERT_TZ(STR_TO_DATE(CONCAT(CONCAT(start_recurring_dt, ' '), start_recurring_hr), '%Y-%m-%d %H:%i:%s'), @@global.time_zone, '+00:00'), ' ', 1)"),
            'start_recurring_hr' => DB::raw("SUBSTRING_INDEX(CONVERT_TZ(STR_TO_DATE(CONCAT(CONCAT(start_recurring_dt, ' '), start_recurring_hr), '%Y-%m-%d %H:%i:%s'), @@global.time_zone, '+00:00'), ' ', -1)"),
            'end_recurring_dt' => DB::raw("SUBSTRING_INDEX(CONVERT_TZ(STR_TO_DATE(CONCAT(CONCAT(end_recurring_dt, ' '), end_recurring_hr), '%Y-%m-%d %H:%i:%s'), @@global.time_zone, '+00:00'), ' ', 1)"),
            'end_recurring_hr' => DB::raw("SUBSTRING_INDEX(CONVERT_TZ(STR_TO_DATE(CONCAT(CONCAT(end_recurring_dt, ' '), end_recurring_hr), '%Y-%m-%d %H:%i:%s'), @@global.time_zone, '+00:00'), ' ', -1)"),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('alert_schedule')->update([
            'start' => DB::raw("CONVERT_TZ(start, '+00:00', @@global.time_zone)"),
            'end' => DB::raw("CONVERT_TZ(end, '+00:00', @@global.time_zone)"),
            'start_recurring_dt' => DB::raw("SUBSTRING_INDEX(CONVERT_TZ(STR_TO_DATE(CONCAT(CONCAT(start_recurring_dt, ' '), start_recurring_hr), '%Y-%m-%d %H:%i:%s'), '+00:00', @@global.time_zone), ' ', 1)"),
            'start_recurring_hr' => DB::raw("SUBSTRING_INDEX(CONVERT_TZ(STR_TO_DATE(CONCAT(CONCAT(start_recurring_dt, ' '), start_recurring_hr), '%Y-%m-%d %H:%i:%s'), '+00:00', @@global.time_zone), ' ', -1)"),
            'end_recurring_dt' => DB::raw("SUBSTRING_INDEX(CONVERT_TZ(STR_TO_DATE(CONCAT(CONCAT(end_recurring_dt, ' '), end_recurring_hr), '%Y-%m-%d %H:%i:%s'), '+00:00', @@global.time_zone), ' ', 1)"),
            'end_recurring_hr' => DB::raw("SUBSTRING_INDEX(CONVERT_TZ(STR_TO_DATE(CONCAT(CONCAT(end_recurring_dt, ' '), end_recurring_hr), '%Y-%m-%d %H:%i:%s'), '+00:00', @@global.time_zone), ' ', -1)"),
        ]);

        Schema::table('alert_schedule', function (Blueprint $table) {
            $table->dateTime('start')->nullable(false)->default('1970-01-02 00:00:01')->change();
            $table->dateTime('end')->nullable(false)->default('1970-01-02 00:00:01')->change();
            $table->date('start_recurring_dt')->nullable(false)->default('1970-01-01')->change();
            $table->time('start_recurring_hr')->nullable(false)->default('00:00:00')->change();
            $table->time('end_recurring_hr')->nullable(false)->default('00:00:00')->change();
        });
    }
}
