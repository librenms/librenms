<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlertsDisableOnUpdateCurrentTimestamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            Schema::table('alerts', function (Blueprint $table) {
                \DB::statement('ALTER TABLE `alerts` CHANGE `timestamp` `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP;');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            Schema::table('alerts', function (Blueprint $table) {
                \DB::statement('ALTER TABLE `alerts` CHANGE `timestamp` `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;');
            });
        }
    }
}
