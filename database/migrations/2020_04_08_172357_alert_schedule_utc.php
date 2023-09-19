<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            DB::table('alert_schedule')->update([
                'start' => DB::raw("CONVERT_TZ(IF(`recurring` = 1, STR_TO_DATE(CONCAT(start_recurring_dt, ' ', start_recurring_hr), '%Y-%m-%d %H:%i:%s'), start), @@global.time_zone, '+00:00')"),
                'end' => DB::raw("CONVERT_TZ(IF(`recurring` = 1, STR_TO_DATE(CONCAT(IFNULL(end_recurring_dt, '9000-09-09'), ' ', end_recurring_hr), '%Y-%m-%d %H:%i:%s'), end), @@global.time_zone, '+00:00')"),
                'recurring_day' => DB::raw('REPLACE(recurring_day, 0, 7)'), // convert to RFC N date format
            ]);
        }

        Schema::table('alert_schedule', function (Blueprint $table) {
            $table->dropColumn(['start_recurring_dt', 'start_recurring_hr', 'end_recurring_dt', 'end_recurring_hr']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('alert_schedule', function (Blueprint $table) {
            $table->date('start_recurring_dt')->nullable(false)->default('1970-01-01')->after('end');
            $table->time('start_recurring_hr')->nullable(false)->default('00:00:00')->after('start_recurring_dt');
            $table->date('end_recurring_dt')->nullable()->after('start_recurring_hr');
            $table->time('end_recurring_hr')->nullable(false)->default('00:00:00')->after('end_recurring_dt');
        });

        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            DB::table('alert_schedule')->update([
                'start' => DB::raw("CONVERT_TZ(start, '+00:00', @@global.time_zone)"),
                'end' => DB::raw("CONVERT_TZ(end, '+00:00', @@global.time_zone)"),
                'start_recurring_dt' => DB::raw("DATE(CONVERT_TZ(start, '+00:00', @@global.time_zone))"),
                'start_recurring_hr' => DB::raw("TIME(CONVERT_TZ(start, '+00:00', @@global.time_zone))"),
                'end_recurring_dt' => DB::raw("DATE(CONVERT_TZ(end, '+00:00', @@global.time_zone))"),
                'end_recurring_hr' => DB::raw("TIME(CONVERT_TZ(end, '+00:00', @@global.time_zone))"),
                'recurring_day' => DB::raw('REPLACE(recurring_day, 7, 0)'),
            ]);
        }
    }
};
